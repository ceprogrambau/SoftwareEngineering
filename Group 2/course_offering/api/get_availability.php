<?php
include '../db.php';

$doctorId = $_GET['doctorId'] ?? '';

$sql = "SELECT * FROM docSchedule WHERE docID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode([
        "monday" => 0,
        "tuesday" => 0,
        "wednesday" => 0,
        "thursday" => 0
    ]);
}
?>
