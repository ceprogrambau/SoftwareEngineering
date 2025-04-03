<?php
header('Content-Type: application/json');
require '../db.php';

$sql = "SELECT docID, docName, email FROM doctors";
$result = $conn->query($sql);

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        'docID' => $row['docID'],
        'docName' => $row['docName'],
        'email' => $row['email']
    ];
}

echo json_encode($doctors);
?>
