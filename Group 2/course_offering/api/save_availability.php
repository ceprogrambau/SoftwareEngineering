<?php
header('Content-Type: application/json');
require '../db.php';

// Get the raw POST data and decode it
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data received: ' . json_last_error_msg()
    ]);
    exit;
}

$doctorId = $data['doctorId'] ?? null;
$availability = $data['availability'] ?? null;

if (!$doctorId || !$availability) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required data: doctorId or availability'
    ]);
    exit;
}

try {
    // First check if the record exists
    $checkStmt = $conn->prepare("SELECT docID FROM docSchedule WHERE docID = ?");
    $checkStmt->bind_param("s", $doctorId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE docSchedule SET monday = ?, tuesday = ?, wednesday = ?, thursday = ? WHERE docID = ?");
        $stmt->bind_param("iiiss", 
            $availability['monday'],
            $availability['tuesday'],
            $availability['wednesday'],
            $availability['thursday'],
            $doctorId
        );
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO docSchedule (docID, monday, tuesday, wednesday, thursday) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiii", 
            $doctorId,
            $availability['monday'],
            $availability['tuesday'],
            $availability['wednesday'],
            $availability['thursday']
        );
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($stmt->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($conn)) $conn->close();
}
?>
