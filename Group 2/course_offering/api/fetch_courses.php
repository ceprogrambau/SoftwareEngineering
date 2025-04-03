<?php
require '../db.php';

$sql = "SELECT courseCode, courseName, credits, lecDuration, labDuration, aYear, semester, singleLec, lec1Duration, lec2Duration, cType, has_lab FROM course";
$result = $conn->query($sql);

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
?>
