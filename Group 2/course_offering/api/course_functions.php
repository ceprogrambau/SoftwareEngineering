<?php
require_once __DIR__ . '/../db.php';

/**
 * Fetches all courses for the view courses page with complete details
 * @return array Array containing success status, message and courses data
 */
function fetchCoursesForView() {
    try {
        $db = Database::getInstance();
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        $sql = "SELECT 
            c.courseCode,
            c.courseName,
            c.credits,
            c.lecDuration,
            c.labDuration,
            c.singleLec,
            c.lec1duration,
            c.lec2duration,
            c.cType,
            c.semester,
            c.has_lab,
            c.aYear,
            c.lab_equipment
        FROM course c 
        ORDER BY c.courseCode ASC";

        $result = $db->query($sql);
        
        if (!$result) {
            throw new Exception($db->error);
        }

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            // Convert and validate each field based on database types
            $courses[] = [
                'courseCode' => trim($row['courseCode']), // CHAR(10)
                'courseName' => trim($row['courseName']), // VARCHAR(255)
                'credits' => intval($row['credits']), // INT
                'lecDuration' => $row['lecDuration'] ? intval($row['lecDuration']) : null, // SMALLINT
                'labDuration' => $row['labDuration'] ? intval($row['labDuration']) : null, // SMALLINT
                'singleLec' => $row['singleLec'] == 1, // BIT(1)
                'lec1duration' => $row['lec1duration'] ? intval($row['lec1duration']) : null, // SMALLINT
                'lec2duration' => $row['lec2duration'] ? intval($row['lec2duration']) : null, // SMALLINT
                'cType' => trim($row['cType'] ?? ''), // VARCHAR(255)
                'semester' => trim($row['semester'] ?? ''), // VARCHAR(10)
                'has_lab' => $row['has_lab'] == 1, // TINYINT
                'aYear' => intval($row['aYear']), // INT
                'lab_equipment' => trim($row['lab_equipment'] ?? '') // VARCHAR(255)
            ];
        }

        $result->free();

        return [
            'success' => true,
            'message' => count($courses) . ' courses found',
            'data' => $courses
        ];

    } catch (Exception $e) {
        error_log("Error in fetchCoursesForView: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch courses: ' . $e->getMessage()
        ];
    }
}

/**
 * Fetches courses for doctor assignment with assignment-specific details
 * @return array Array containing success status, message and courses data
 */
function fetchCoursesForAssignment() {
    try {
        $db = Database::getInstance();
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        $sql = "SELECT 
            c.courseCode,
            c.courseName,
            c.has_lab,
            c.credits,
            c.singleLec,
            SUM(CASE WHEN dtc.isLecturer = 1 THEN 1 ELSE 0 END) as lecturer_count,
            SUM(CASE WHEN dtc.isLabInstructor = 1 THEN 1 ELSE 0 END) as lab_instructor_count
        FROM course c
        LEFT JOIN doc_teach_course dtc ON c.courseCode = dtc.courseCode
        GROUP BY 
            c.courseCode, 
            c.courseName, 
            c.has_lab, 
            c.credits, 
            c.singleLec
        ORDER BY c.courseCode ASC";

        $result = $db->query($sql);
        
        if (!$result) {
            throw new Exception($db->error);
        }

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $lecturer_count = intval($row['lecturer_count']);
            $lab_instructor_count = intval($row['lab_instructor_count']);
            $has_lab = $row['has_lab'] == 1;

            $courses[] = [
                'courseCode' => trim($row['courseCode']),
                'courseName' => trim($row['courseName']),
                'has_lab' => $has_lab,
                'credits' => intval($row['credits']),
                'singleLec' => $row['singleLec'] == 1,
                'lecturer_count' => $lecturer_count,
                'lab_instructor_count' => $lab_instructor_count,
                'needs_lecturer' => $lecturer_count === 0,
                'needs_lab_instructor' => $has_lab && $lab_instructor_count === 0
            ];
        }

        $result->free();

        return [
            'success' => true,
            'message' => count($courses) . ' courses found',
            'data' => $courses
        ];

    } catch (Exception $e) {
        error_log("Error in fetchCoursesForAssignment: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch courses: ' . $e->getMessage()
        ];
    }
}