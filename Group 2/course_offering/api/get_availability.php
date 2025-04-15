<?php



//used for editing the availablity of the doctor 



header("Content-Type: application/json");
include '../db.php'; // ✅ Fixed the relative path

if (!isset($_GET['doctorId'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing doctor ID"
    ]);
    exit;
}

$docID = $_GET['doctorId'];

try {
    $query = "SELECT monday, tuesday, wednesday, thursday FROM docSchedule WHERE docID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $docID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    } else {
        // Return default empty availability if doctor not found
        echo json_encode([
            "success" => true,
            "data" => [
                "monday" => 0,
                "tuesday" => 0,
                "wednesday" => 0,
                "thursday" => 0
            ]
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
