<?php

//used for fetching the doctors from the database for the table 
header('Content-Type: application/json');
require '../db.php';

try {
    $courseCode = isset($_GET['courseCode']) ? $_GET['courseCode'] : null;
    $purpose = isset($_GET['purpose']) ? $_GET['purpose'] : 'assignment';

    // For availability management, we don't need course code
    if ($purpose === 'availability') {
        $sql = "SELECT docID, docName, email FROM doctors ORDER BY docName";
        $stmt = $conn->prepare($sql);
    } else {
        // For course assignments, we need course code
        if (!$courseCode) {
            throw new Exception("Course code is required");
        }

        $sql = "SELECT 
            d.docID, 
            d.docName, 
            d.email,
            MAX(CASE WHEN dtc.isLecturer = 1 THEN 1 ELSE 0 END) as isLecturer,
            MAX(CASE WHEN dtc.isLabInstructor = 1 THEN 1 ELSE 0 END) as isLabInstructor
        FROM doctors d
        LEFT JOIN doc_teach_course dtc ON d.docID = dtc.docID AND dtc.courseCode = ?
        GROUP BY d.docID, d.docName, d.email
        ORDER BY d.docName";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $courseCode);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Getting result failed: " . $stmt->error);
    }

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        // For availability, we only need basic doctor info
        if ($purpose === 'availability') {
            $doctors[] = [
                'docID' => $row['docID'],
                'docName' => $row['docName'],
                'email' => $row['email']
            ];
        } else {
            // For assignments, include assignment status
            $doctors[] = [
                'docID' => $row['docID'],
                'docName' => $row['docName'],
                'email' => $row['email'],
                'isLecturer' => (bool)$row['isLecturer'],
                'isLabInstructor' => (bool)$row['isLabInstructor']
            ];
        }
    }

    echo json_encode([
        'success' => true, 
        'data' => $doctors
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
