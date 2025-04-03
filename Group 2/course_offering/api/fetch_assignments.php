<?php
header('Content-Type: application/json');
require '../db.php';

$sql = "SELECT 
            d.docName,
            d.email,
            c.courseCode,
            c.courseName,
            IF(dtc.isLabInstructor = 1, 'Yes', IF(dtc.isLabInstructor = 0, 'No', 'N/A')) AS isLabInstructor
        FROM doc_teach_course dtc
        JOIN doctors d ON dtc.docID = d.docID
        JOIN course c ON dtc.courseCode = c.courseCode
        ORDER BY c.courseCode, dtc.isLabInstructor DESC";

$result = $conn->query($sql);
$assignments = [];

while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

echo json_encode($assignments);
?>
