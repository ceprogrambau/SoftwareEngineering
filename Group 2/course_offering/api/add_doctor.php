<?php
require '../db.php';

$docID = $_POST['doctorID'];
$docName = $_POST['doctorName'];
$email = $_POST['email'];

$sql = "INSERT INTO doctors (docID, docName, email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $docID, $docName, $email);

if ($stmt->execute()) {
    header("Location: add_doctor.html?message=Doctor added successfully!");
} else {
    header("Location: add_doctor.html?message=Error: " . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
?>