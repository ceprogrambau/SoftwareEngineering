<?php



//used for editing the availablity of the doctor 



header("Content-Type: application/json");
include '../db.php'; // ✅ Fixed the relative path

if (!isset($_GET['doctorId'])) {
    echo json_encode(["error" => "Missing doctor ID"]);
    exit;
}

$docID = $_GET['doctorId'];

$query = "SELECT monday, tuesday, wednesday, thursday FROM docSchedule WHERE docID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $docID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    // Return default empty availability if doctor not found
    echo json_encode([
        "monday" => 0,
        "tuesday" => 0,
        "wednesday" => 0,
        "thursday" => 0
    ]);
}
?>
