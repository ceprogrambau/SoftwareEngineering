<?php

//used for fetching the doctors from the database for the table 
header('Content-Type: application/json');
require '../db.php';

try {
    $sql = "SELECT docID, docName, email FROM doctors ORDER BY docName";  // Added ORDER BY for better display
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = [
            'docID' => $row['docID'],
            'docName' => $row['docName'],
            'email' => $row['email']
        ];
    }

    echo json_encode(['success' => true, 'data' => $doctors]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
