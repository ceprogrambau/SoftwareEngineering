<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("Attempting database connection to remote server...");

// Remote database settings
$host = '35.184.223.196';
$user = 'root';
$password = '=6)r6YQKq7iry[,v';
$dbname = 'course';

// Set longer timeout for remote connection
$conn = mysqli_init();
if (!$conn) {
    die(json_encode([
        'success' => false,
        'message' => 'mysqli_init failed'
    ]));
}

// Set connection timeout to 10 seconds
if (!mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10)) {
    die(json_encode([
        'success' => false,
        'message' => 'Setting MYSQLI_OPT_CONNECT_TIMEOUT failed'
    ]));
}

try {
    if (!mysqli_real_connect($conn, $host, $user, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to ensure proper encoding
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error setting charset: " . $conn->error);
    }

    error_log("Successfully connected to remote database");

} catch (Exception $e) {
    error_log("Critical database error: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}

?>
