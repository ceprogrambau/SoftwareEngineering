<?php
header("Content-Type: text/html; charset=utf-8");
include '../db.php';  // Adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize POST data
    $doctorName = trim($_POST['doctorName']);
    $email      = trim($_POST['email']);
    $doctorID   = trim($_POST['doctorID']);

    // Optional: more robust validation here (e.g., check valid email format)
    if (empty($doctorName) || empty($email) || empty($doctorID)) {
        $message = "Please fill in all fields.";
    } else {
        // Prepare SQL insert statement for the "doctors" table
        $stmt = $conn->prepare("INSERT INTO doctors (docID, docName, email) VALUES (?, ?, ?)");
        if (!$stmt) {
            $message = "Prepare failed: " . $conn->error;
        } else {
            // Bind the values: docID, docName, email
            $stmt->bind_param("sss", $doctorID, $doctorName, $email);

            if ($stmt->execute()) {
                $message = "Doctor added successfully.";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    $conn->close();
    // Redirect back to the add_doctor.html with a status message
    header("Location: ../pages/add_doctor.html?message=" . urlencode($message));
    exit;
} else {
    // If not POST, redirect to add_doctor.html
    header("Location: ../pages/add_doctor.html");
    exit;
}
?>
