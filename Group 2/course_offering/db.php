<?php
$host = '35.184.223.196';
$user = 'root';
$password = '=6)r6YQKq7iry[,v';
$dbname = 'course';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
