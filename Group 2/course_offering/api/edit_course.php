<?php
require '../db.php';

$code = $_POST['courseCode'];
$name = $_POST['courseName'];
$semester = $_POST['semester'] === 'Fall' ? 'F' : 'S';
$year = (int)$_POST['yearLevel'];  // INT
$category = $_POST['category'];   // VARCHAR
$credits = (int)$_POST['credits']; // INT
$hasLab = $_POST['hasLab'] === 'yes' ? 1 : 0; // TINYINT
$labDuration = $_POST['labDuration'] !== '' ? (int)$_POST['labDuration'] : null; // SMALLINT
$isDivided = $_POST['isDivided'] === 'yes' ? 0 : 1; // BIT (0 if divided)
$lecDuration = (int)$_POST['lecDuration']; // SMALLINT
$lec1 = $_POST['lec1Duration'] !== '' ? (int)$_POST['lec1Duration'] : null;
$lec2 = $_POST['lec2Duration'] !== '' ? (int)$_POST['lec2Duration'] : null;

// SQL update statement
$sql = "UPDATE course SET 
            courseName = ?, 
            credits = ?, 
            lecDuration = ?, 
            labDuration = ?, 
            aYear = ?, 
            semester = ?, 
            singleLec = ?, 
            lec1Duration = ?, 
            lec2Duration = ?, 
            cType = ?, 
            has_lab = ?
        WHERE courseCode = ?";

$stmt = $conn->prepare($sql);

// ✅ Correct bind types based on your ER diagram
$stmt->bind_param(
    "siiiisiiisis",
    $name,        // s: courseName
    $credits,     // i: credits
    $lecDuration, // i: lecDuration
    $labDuration, // i: labDuration
    $year,        // i: aYear
    $semester,    // s: semester
    $isDivided,   // i: singleLec
    $lec1,        // i: lec1duration
    $lec2,        // i: lec2duration
    $category,    // ✅ s: cType (this fixes your issue!)
    $hasLab,      // i: has_lab
    $code         // s: courseCode (WHERE)
);

// Run and respond
if ($stmt->execute()) {
    echo "✅ Course updated successfully. <a href='view_courses.html'>Back to Course List</a>";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
