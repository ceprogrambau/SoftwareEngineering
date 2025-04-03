<?php
header('Content-Type: application/json');
include '../db.php';

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['doctorId']) || !isset($data['availability'])) {
    echo json_encode(["error" => "Invalid input data"]);
    exit();
}

$doctorId = $data['doctorId'];
$availability = $data['availability'];

$monday = $availability['Monday'];
$tuesday = $availability['Tuesday'];
$wednesday = $availability['Wednesday'];
$thursday = $availability['Thursday'];

$sql = "INSERT INTO docSchedule (docID, monday, tuesday, wednesday, thursday)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            monday = VALUES(monday),
            tuesday = VALUES(tuesday),
            wednesday = VALUES(wednesday),
            thursday = VALUES(thursday)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL error: " . $conn->error]);
    exit();
}

$stmt->bind_param("siiii", $doctorId, $monday, $tuesday, $wednesday, $thursday);

if ($stmt->execute()) {
    echo json_encode(["success" => "Availability saved successfully"]);
} else {
    echo json_encode(["error" => "Error saving: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
