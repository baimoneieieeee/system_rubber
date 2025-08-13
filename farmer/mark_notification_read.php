<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    echo json_encode(['success' => false, 'message' => 'ไม่ได้รับอนุญาต']);
    exit();
}

$farmer_id = $_SESSION['farmer_id'];
$notification_id = $_POST['notification_id'] ?? '';

if (empty($notification_id)) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

// อัพเดตสถานะแจ้งเตือนเป็นอ่านแล้ว เฉพาะของเกษตรกรคนนี้เท่านั้น
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND farmer_id = ?");
$stmt->bind_param("ii", $notification_id, $farmer_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'อัพเดตข้อมูลล้มเหลว']);
}
