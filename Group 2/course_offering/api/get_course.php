<?php
require '../db.php';

$code = $_GET['code'] ?? '';

$sql = "SELECT * FROM course WHERE courseCode = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Course not found."]);
}
?>
