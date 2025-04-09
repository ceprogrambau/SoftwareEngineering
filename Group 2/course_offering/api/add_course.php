<?php
// Include your database connection configuration file
require_once('../db.php');

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Retrieve and sanitize form inputs
    $courseCode  = trim($_POST['courseCode']);
    $courseName  = trim($_POST['courseName']);
    $semester    = trim($_POST['semester']);
    $yearLevel   = trim($_POST['yearLevel']);
    $category    = trim($_POST['category']);
    $credits     = intval($_POST['credits']);

    // Optional numeric fields; default to 0 if not provided or invalid
    $labDuration  = (isset($_POST['labDuration']) && is_numeric($_POST['labDuration'])) ? intval($_POST['labDuration']) : 0;
    $lecDuration  = (isset($_POST['lecDuration']) && is_numeric($_POST['lecDuration'])) ? intval($_POST['lecDuration']) : 0;
    $lec1Duration = (isset($_POST['lec1Duration']) && is_numeric($_POST['lec1Duration'])) ? intval($_POST['lec1Duration']) : 0;
    
    // Calculate lec2Duration as total lecture time minus lecture 1 time (ensure it's not negative)
    $lec2Duration = max(0, $lecDuration - $lec1Duration);

    // Optional: validate that lec1Duration is not greater than lecDuration
    if ($lec1Duration > $lecDuration) {
        die("Lecture 1 duration cannot exceed total lecture duration.");
    }

    // Prepare the SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO course (courseCode, courseName, semester, aYear, cType, credits, labDuration, lecDuration, lec1Duration, lec2Duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    // Bind parameters:
    // "sssssiiiii" means:
    //   - 5 strings: courseCode, courseName, semester, yearLevel, category
    //   - 5 integers: credits, labDuration, lecDuration, lec1Duration, lec2Duration
    $stmt->bind_param("sssssiiiii", $courseCode, $courseName, $semester, $yearLevel, $category, $credits, $labDuration, $lecDuration, $lec1Duration, $lec2Duration);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Course added successfully.";
    } else {
        echo "Error adding course: " . $stmt->error;
    }

    // Clean up
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
