<?php
header("Content-Type: application/json");
include '../db.php';

// Retrieve form data from POST
$courseCode = trim($_POST['courseCode']);
$courseName = trim($_POST['courseName']);
$semester = $_POST['semester'];
$yearLevel = $_POST['yearLevel'];
$category = $_POST['category'];
$credits = intval($_POST['credits']);

// Convert semester name to single character for DB
$semesterDB = ($semester === "Fall") ? "F" : "S";

// Get durations
$lecDuration = intval($_POST['lecDuration']);
$lec1Duration = !empty($_POST['lec1Duration']) ? intval($_POST['lec1Duration']) : $lecDuration;
$lec2Duration = intval($_POST['lec2Duration']);
$labDuration = intval($_POST['labDuration']);

// Derive has_lab from labDuration
$has_lab = ($labDuration > 0) ? 1 : 0;

// Derive singleLec from lec2Duration
$singleLec = ($lec2Duration == 0) ? 1 : 0;

// Lab equipment is only required if has_lab is true
$lab_equipment = ($has_lab == 1) ? $_POST['labEquipment'] : null;

// Validate the data
if ($lec1Duration > $lecDuration) {
    die(json_encode([
        'success' => false,
        'message' => 'First lecture duration cannot exceed total lecture duration'
    ]));
}

if ($has_lab && empty($lab_equipment)) {
    die(json_encode([
        'success' => false,
        'message' => 'Lab equipment type is required for courses with lab'
    ]));
}

$stmt = $conn->prepare("
    UPDATE course
    SET 
        courseName = ?, 
        semester = ?, 
        aYear = ?, 
        cType = ?, 
        credits = ?, 
        has_lab = ?, 
        singleLec = ?, 
        lecDuration = ?, 
        labDuration = ?, 
        lec1Duration = ?, 
        lec2Duration = ?,
        lab_equipment = ?
    WHERE courseCode = ?
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => "Prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ssisiiiiiiiss",
    $courseName,
    $semesterDB,
    $yearLevel,
    $category,
    $credits,
    $has_lab,
    $singleLec,
    $lecDuration,
    $labDuration,
    $lec1Duration,
    $lec2Duration,
    $lab_equipment,
    $courseCode
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Course updated successfully"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Database update failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
