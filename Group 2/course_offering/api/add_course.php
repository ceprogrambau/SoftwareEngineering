<?php
require '../db.php';

function clean($value) {
    return isset($value) && $value !== '' ? $value : NULL;
}

$courseCode = $_POST['courseCode'];
$courseName = $_POST['courseName'];
$credits = $_POST['credits']; // ✅ New: read number of credits

$semester = $_POST['semester'] === 'Fall' ? 'F' : 'S';
$year = $_POST['yearLevel'];
$type = $_POST['category'];

$lecDuration = $_POST['lecDuration'];
$labDuration = clean($_POST['labDuration'] ?? null);
$lec1Duration = clean($_POST['lec1Duration'] ?? null);
$lec2Duration = clean($_POST['lec2Duration'] ?? null);

$hasLab = $_POST['hasLab'] === 'yes' ? 1 : 0;
$singleLec = $_POST['isDivided'] === 'yes' ? 0 : 1;

// Validate lecture durations if split
if ($singleLec == 0 && ($lec1Duration === NULL || $lec2Duration === NULL || ($lec1Duration + $lec2Duration) != $lecDuration)) {
    die("Lecture durations do not match total duration.");
}

// ✅ Updated SQL to include credits
$sql = "INSERT INTO course (courseCode, courseName, credits, lecDuration, labDuration, aYear, semester, singleLec, lec1Duration, lec2Duration, cType, has_lab)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            courseName = VALUES(courseName),
            credits = VALUES(credits),
            lecDuration = VALUES(lecDuration),
            labDuration = VALUES(labDuration),
            aYear = VALUES(aYear),
            semester = VALUES(semester),
            singleLec = VALUES(singleLec),
            lec1Duration = VALUES(lec1Duration),
            lec2Duration = VALUES(lec2Duration),
            cType = VALUES(cType),
            has_lab = VALUES(has_lab)";


$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiissiisisi", $courseCode, $courseName, $credits, $lecDuration, $labDuration, $year, $semester, $singleLec, $lec1Duration, $lec2Duration, $type, $hasLab);

if ($stmt->execute()) {
    echo "Course added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
