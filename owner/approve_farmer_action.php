<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id'])) {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

if (isset($_GET['farmer_id'], $_GET['action'])) {
    $farmer_id = intval($_GET['farmer_id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        // อัปเดตข้อมูลเกษตรกรให้ผูกกับเจ้าของสวน และตั้งสถานะอนุมัติ
        $sql = "UPDATE farmers SET owner_id = ?, is_approved = 1, status = 'approved' WHERE farmer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $owner_id, $farmer_id);
        $stmt->execute();
    } elseif ($action === 'reject') {
        // อัปเดตสถานะเป็นปฏิเสธ
        $sql = "UPDATE farmers SET status = 'rejected' WHERE farmer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $farmer_id);
        $stmt->execute();
    }
}

header('Location: add_farmer.php');
exit();
