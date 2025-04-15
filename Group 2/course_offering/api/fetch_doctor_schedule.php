<?php
include '../db.php';

$sql = "SELECT d.docID, d.docName, s.monday, s.tuesday, s.wednesday, s.thursday
        FROM doctors d
        INNER JOIN docSchedule s ON d.docID = s.docID
        ORDER BY d.docID";

$result = $conn->query($sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
?>
