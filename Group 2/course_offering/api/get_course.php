<?php
require '../db.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if (empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Course code is required'
    ]);
    exit;
}

$sql = "SELECT * FROM course WHERE courseCode = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'data' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Course not found'
    ]);
}

$stmt->close();
$conn->close();
?>
