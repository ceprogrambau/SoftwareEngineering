<?php
header('Content-Type: application/json');
require '../db.php';

$sql = "SELECT 
            d.docName,
            d.email,
            d.docID,
            c.courseCode,
            c.courseName,
            CASE 
                WHEN dtc.isLecturer = 1 THEN 'Lecturer'
                WHEN dtc.isLabInstructor = 1 THEN 'Lab Instructor'
                ELSE 'Unknown'
            END as role
        FROM doc_teach_course dtc
        JOIN doctors d ON dtc.docID = d.docID
        JOIN course c ON dtc.courseCode = c.courseCode
        ORDER BY c.courseCode, dtc.isLecturer DESC, dtc.isLabInstructor DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $conn->error]);
    exit;
}

$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

echo json_encode(['success' => true, 'data' => $assignments]);

$conn->close();
?>
