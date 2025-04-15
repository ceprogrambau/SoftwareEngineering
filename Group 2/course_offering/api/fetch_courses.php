<?php
// Prevent any unwanted output
error_reporting(E_ERROR);
ini_set('display_errors', 0);

// Allow CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

// Clear any previous output
if (ob_get_length()) ob_clean();

require_once '../db.php';

// Check if database connection exists
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    $purpose = isset($_GET['purpose']) ? $_GET['purpose'] : '';
    
    if ($purpose === 'assignment') {
        // Query for assignment page with lecturer and lab instructor counts
        $sql = "SELECT 
            c.courseCode, 
            c.courseName,
            c.labDuration > 0 as has_lab,
            (SELECT COUNT(*) FROM doc_teach_course dtc 
             WHERE dtc.courseCode = c.courseCode 
             AND dtc.isLecturer = 1) as lecturer_count,
            (SELECT COUNT(*) FROM doc_teach_course dtc 
             WHERE dtc.courseCode = c.courseCode 
             AND dtc.isLabInstructor = 1) as lab_instructor_count
        FROM course c
        ORDER BY c.courseCode ASC";
    } else {
        // Default query for other purposes
        $sql = "SELECT 
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
            labDuration > 0 as has_lab,
            lab_equipment
        FROM course 
        ORDER BY courseCode ASC";
    }

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception($conn->error);
    }

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Convert has_lab to boolean for JSON
        if (isset($row['has_lab'])) {
            $row['has_lab'] = (bool)$row['has_lab'];
        }
        $courses[] = $row;
    }

    echo json_encode([
        'success' => true,
        'message' => count($courses) . ' courses found',
        'data' => $courses
    ]);

} catch (Exception $e) {
    error_log("Error in fetch_courses.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch courses: ' . $e->getMessage()
    ]);
}
