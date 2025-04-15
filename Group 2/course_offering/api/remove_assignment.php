<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['courseCode']) || !isset($data['docID'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$courseCode = $data['courseCode'];
$docID = $data['docID'];
$role = $data['role'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Determine which field to set to 0 based on role
    if ($role === 'Lecturer') {
        $updateField = 'isLecturer';
    } else if ($role === 'Lab Instructor') {
        $updateField = 'isLabInstructor';
    } else {
        throw new Exception('Invalid role specified');
    }

    // Update the assignment
    $sql = "UPDATE doc_teach_course SET $updateField = 0 WHERE courseCode = ? AND docID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $courseCode, $docID);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // If both isLecturer and isLabInstructor are 0, remove the record entirely
    $sql = "DELETE FROM doc_teach_course 
            WHERE courseCode = ? AND docID = ? 
            AND isLecturer = 0 AND isLabInstructor = 0";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed for cleanup: " . $conn->error);
    }

    $stmt->bind_param("ss", $courseCode, $docID);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed for cleanup: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully removed $role assignment"
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error removing assignment: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
} 