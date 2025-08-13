<?php
// ... ฟังก์ชันอื่น ๆ ...

function addNotification($conn, $owner_id, $message)
{
    $stmt = $conn->prepare("INSERT INTO notifications (owner_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $owner_id, $message);
    $stmt->execute();
    $stmt->close();
}
