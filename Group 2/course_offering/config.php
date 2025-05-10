<?php
// Database Configuration
define('DB_HOST', '35.184.223.196');
define('DB_USER', 'root');
define('DB_PASS', '=6)r6YQKq7iry[,v');
define('DB_NAME', 'course');
define('DB_PORT', 3306);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Database Connection Options
define('DB_CONNECT_TIMEOUT', 10);
define('DB_CHARSET', 'utf8mb4');

// API Response Settings
define('JSON_RESPONSE_HEADER', 'Content-Type: application/json; charset=UTF-8');
?> 