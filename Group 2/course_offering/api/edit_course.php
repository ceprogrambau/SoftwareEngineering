<?php
header("Content-Type: application/json");
include '../db.php';

// Retrieve form data from POST
$courseCode   = trim($_POST['courseCode']);
$courseName   = trim($_POST['courseName']);
$semester     = $_POST['semester'];

// Convert semester name to single character for DB
if ($semester === "Fall") {
    $semesterDB = "F";
} else if ($semester === "Spring") {
    $semesterDB = "S";
} else {
    $semesterDB = $semester;
}

// Map form fields to DB columns
$aYear = $_POST['yearLevel'];
$cType = $_POST['category'];
$credits = $_POST['credits'];

// Process lab info: if no lab, force labDuration to 0.
$hasLab = ($_POST['hasLab'] === "yes") ? 1 : 0;
$labDuration = ($hasLab === 1 && isset($_POST['labDuration']) && $_POST['labDuration'] !== "") ? $_POST['labDuration'] : 0;

// Process lecture durations:
$lecDuration = $_POST['lecDuration'] ?: 0;
$isDivided = ($_POST['isDivided'] === "yes") ? 0 : 1;
if ($isDivided === 1) {
    // For divided courses, use provided lec1Duration and compute lec2Duration
    $lec1Duration = (isset($_POST['lec1Duration']) && $_POST['lec1Duration'] !== "") ? $_POST['lec1Duration'] : 0;
    $lec2Duration = ($lec1Duration > 0 && $lecDuration >= $lec1Duration) ? ($lecDuration - $lec1Duration) : 0;
} else {
    // For single lecture courses, assign total to lec1 and set lec2 to 0.
    $lec1Duration = $lecDuration;
    $lec2Duration = 0;
}

$stmt = $conn->prepare("
  UPDATE course
  SET 
    courseName    = ?, 
    semester      = ?, 
    aYear         = ?, 
    cType         = ?, 
    credits       = ?, 
    has_lab       = ?, 
    singleLec     = ?, 
    lecDuration   = ?, 
    labDuration   = ?, 
    lec1Duration  = ?, 
    lec2Duration  = ?
  WHERE courseCode = ?
");

if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssisiiiiiiis",
    $courseName,
    $semesterDB,
    $aYear,
    $cType,
    $credits,
    $hasLab,
    $isDivided,
    $lecDuration,
    $labDuration,
    $lec1Duration,
    $lec2Duration,
    $courseCode
);

if ($stmt->execute()) {
    echo json_encode(["success" => "Course updated successfully"]);
} else {
    echo json_encode(["error" => "Database update failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
