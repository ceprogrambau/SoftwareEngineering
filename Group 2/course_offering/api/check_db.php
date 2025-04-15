<?php
header('Content-Type: application/json; charset=UTF-8');
require '../db.php';

try {
    $diagnostics = [
        'database_connection' => false,
        'table_exists' => false,
        'table_structure' => [],
        'sample_data' => [],
        'errors' => []
    ];

    // Check database connection
    if ($conn && !$conn->connect_error) {
        $diagnostics['database_connection'] = true;
    } else {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'course'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $diagnostics['table_exists'] = true;
    } else {
        // If table doesn't exist, create it
        $createTable = "CREATE TABLE IF NOT EXISTS course (
            courseCode VARCHAR(10) PRIMARY KEY,
            courseName VARCHAR(100) NOT NULL,
            has_lab TINYINT(1) DEFAULT 0,
            credits INT DEFAULT 3,
            lecture_duration INT DEFAULT 0,
            lab_duration INT DEFAULT 0,
            year INT,
            semester INT,
            lecture_mode VARCHAR(50),
            category VARCHAR(50) DEFAULT 'General'
        )";
        
        if ($conn->query($createTable)) {
            $diagnostics['table_created'] = true;
            
            // Insert sample data
            $sampleData = "INSERT INTO course (courseCode, courseName, has_lab, credits) VALUES
                ('CS101', 'Introduction to Programming', 1, 3),
                ('CS102', 'Data Structures', 1, 3)
            ";
            if ($conn->query($sampleData)) {
                $diagnostics['sample_data_inserted'] = true;
            }
        }
    }

    // Get table structure
    $structure = $conn->query("DESCRIBE course");
    if ($structure) {
        while ($row = $structure->fetch_assoc()) {
            $diagnostics['table_structure'][] = $row;
        }
    }

    // Get sample data
    $sample = $conn->query("SELECT * FROM course LIMIT 3");
    if ($sample) {
        while ($row = $sample->fetch_assoc()) {
            $diagnostics['sample_data'][] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'diagnostics' => $diagnostics
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Diagnostic error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'diagnostics' => $diagnostics ?? []
    ], JSON_PRETTY_PRINT);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
} 