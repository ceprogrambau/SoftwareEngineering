<?php
header("Content-Type: application/json");
include '../db.php';  // Adjust path if needed

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['courseCode']) || !isset($data['assignments'])) {
    echo json_encode(["success" => false, "message" => "Missing required data: courseCode or assignments."]);
    exit;
}

$courseCode   = $data['courseCode'];          // e.g. "COMP888"
$assignments  = $data['assignments'];         // array of { docID, isLecturer, isLabInstructor }
$courseHasLab = isset($data['courseHasLab']) ? $data['courseHasLab'] : false;

// Validate that assignments is an array and not empty
if (!is_array($assignments) || empty($assignments)) {
    echo json_encode(["success" => false, "message" => "Assignments must be a non-empty array."]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1) Validate that the course exists and check its lab status
    $courseSQL = "SELECT labDuration > 0 as has_lab FROM course WHERE courseCode = ?";
    $courseStmt = $conn->prepare($courseSQL);
    if (!$courseStmt) {
        throw new Exception("Course validation prepare failed: " . $conn->error);
    }
    $courseStmt->bind_param("s", $courseCode);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    
    if ($courseResult->num_rows === 0) {
        throw new Exception("Course '$courseCode' does not exist in the database.");
    }
    
    $courseData = $courseResult->fetch_assoc();
    $dbHasLab = (bool)$courseData['has_lab'];
    
    // Convert courseHasLab to boolean for comparison
    $courseHasLab = filter_var($courseHasLab, FILTER_VALIDATE_BOOLEAN);
    
    // Ensure courseHasLab matches the database
    if ($courseHasLab !== $dbHasLab) {
        throw new Exception("Course lab status mismatch with database.");
    }
    $courseStmt->close();

    // 2) Remove any old assignments for this course
    $deleteSQL = "DELETE FROM doc_teach_course WHERE courseCode = ?";
    $delStmt = $conn->prepare($deleteSQL);
    if (!$delStmt) {
        throw new Exception("Delete prepare failed: " . $conn->error);
    }
    $delStmt->bind_param("s", $courseCode);
    if (!$delStmt->execute()) {
        throw new Exception("Delete execute failed: " . $delStmt->error);
    }
    $delStmt->close();

    // 3) Validate that all doctor IDs exist
    $validateSQL = "SELECT docID FROM doctors WHERE docID = ?";  // Changed 'doctor' to 'doctors' to match ERD
    $validateStmt = $conn->prepare($validateSQL);
    if (!$validateStmt) {
        throw new Exception("Validation prepare failed: " . $conn->error);
    }

    // Track if we have at least one lecturer assigned
    $hasLecturer = false;
    
    foreach ($assignments as $assign) {
        if (!isset($assign['docID']) || !isset($assign['isLecturer']) || !isset($assign['isLabInstructor'])) {
            throw new Exception("Invalid assignment data: missing required fields.");
        }
        
        $docID = $assign['docID'];
        $validateStmt->bind_param("s", $docID);
        $validateStmt->execute();
        $result = $validateStmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Doctor ID '$docID' does not exist in the database.");
        }

        if ($assign['isLecturer']) {
            $hasLecturer = true;
        }
    }
    $validateStmt->close();

    // Ensure at least one lecturer is assigned
    if (!$hasLecturer) {
        throw new Exception("At least one lecturer must be assigned to the course.");
    }

    // 4) Insert new assignments
    $insertSQL = "
        INSERT INTO doc_teach_course (courseCode, docID, isLecturer, isLabInstructor)
        VALUES (?, ?, ?, ?)
    ";
    $insStmt = $conn->prepare($insertSQL);
    if (!$insStmt) {
        throw new Exception("Insert prepare failed: " . $conn->error);
    }

    foreach ($assignments as $assign) {
        $docID          = $assign['docID'];
        $isLecturer     = intval($assign['isLecturer']);       // 0 or 1
        $isLabInstructor = intval($assign['isLabInstructor']);  // 0 or 1
        
        // If course doesn't have lab, ensure isLabInstructor is always 0
        if (!$courseHasLab) {
            $isLabInstructor = 0;
        }

        // Bind: courseCode (string), docID (string), isLecturer (int), isLabInstructor (int)
        $insStmt->bind_param("ssii", $courseCode, $docID, $isLecturer, $isLabInstructor);
        if (!$insStmt->execute()) {
            throw new Exception("DB error during insertion: " . $insStmt->error);
        }
    }

    $insStmt->close();
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode(["success" => true, "message" => "Assignments saved successfully."]);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
}
