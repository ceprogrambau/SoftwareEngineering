<?php
header('Content-Type: application/json');
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$courseCode = $data['courseCode'];
$assignments = $data['assignments'];
$courseHasLab = $data['courseHasLab'];

if (!$courseCode || !is_array($assignments)) {
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

// Remove old assignments
$delete = $conn->prepare("DELETE FROM doc_teach_course WHERE courseCode = ?");
$delete->bind_param("s", $courseCode);
$delete->execute();

// Insert updated assignments
$stmt = $conn->prepare("INSERT INTO doc_teach_course (courseCode, docID, isLabInstructor) VALUES (?, ?, ?)");
foreach ($assignments as $a) {
    $docID = $a['docID'];
    $isLab = $courseHasLab ? ($a['isLabInstructor'] ?? 0) : null;
    $stmt->bind_param("ssi", $courseCode, $docID, $isLab);
    $stmt->execute();
}

echo json_encode(["message" => "Assignments updated successfully"]);
?>
