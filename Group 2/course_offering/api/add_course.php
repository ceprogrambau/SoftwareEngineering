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

    // Prepare the SQL INSERT statement
    $sql = "INSERT INTO course (
        courseCode, 
        courseName, 
        credits, 
        lecDuration, 
        labDuration, 
        aYear, 
        semester, 
        singleLec, 
        lec1Duration, 
        lec2Duration, 
        cType,
        has_lab,
        lab_equipment
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode([
            'success' => false,
            'message' => "Prepare failed: " . $conn->error
        ]));
    }

    // Bind parameters:
    // "sssssiiiii" means:
    //   - 5 strings: courseCode, courseName, semester, yearLevel, category
    //   - 5 integers: credits, lecDuration, labDuration, lec1Duration, lec2Duration
    //   - 1 boolean: has_lab
    //   - 1 string: lab_equipment
    $stmt->bind_param("ssiiiisisssiss", 
        $courseCode,
        $courseName, 
        $credits, 
        $lecDuration, 
        $labDuration, 
        $yearLevel, 
        $semester, 
        $singleLec, 
        $lec1Duration, 
        $lec2Duration, 
        $category,
        $has_lab,
        $lab_equipment
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "Course added successfully"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Error adding course: " . $stmt->error
        ]);
    }

    // Clean up
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => "Invalid request method"
    ]);
}
?>
