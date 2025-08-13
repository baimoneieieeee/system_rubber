<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];
$message = "";

// กำหนดระยะเวลาการแจ้งเตือน (วัน)
$fertilizer_threshold_days = 30;  // ใส่ปุ๋ยทุก 30 วัน
$latex_collection_threshold_days = 7;  // เก็บน้ำยางทุก 7 วัน

// ฟังก์ชันเพิ่มแจ้งเตือน (ถ้ายังไม่มีแจ้งเตือนซ้ำที่ยังไม่อ่าน)
function addNotification($conn, $farmer_id, $owner_id, $message)
{
    $sql_check = "SELECT COUNT(*) as cnt FROM notifications WHERE farmer_id = ? AND owner_id = ? AND message = ? AND is_read = 0";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iis", $farmer_id, $owner_id, $message);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result()->fetch_assoc();
    if ($res_check['cnt'] == 0) {
        $sql_insert = "INSERT INTO notifications (farmer_id, owner_id, message, is_read) VALUES (?, ?, ?, 0)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iis", $farmer_id, $owner_id, $message);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// เช็คแจ้งเตือนใหม่ก่อนแสดง
$today = new DateTime();

$sql_farmers = "SELECT farmer_id, farmer_name FROM farmers WHERE owner_id = ?";
$stmt = $conn->prepare($sql_farmers);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

while ($farmer = $result->fetch_assoc()) {
    $farmer_id = $farmer['farmer_id'];
    $farmer_name = $farmer['farmer_name'];

    // เช็ควันล่าสุดใส่ปุ๋ย
    $sql_fert = "SELECT MAX(fertilizer_date) AS last_fert_date FROM fertilizer_records WHERE farmer_id = ?";
    $stmt_fert = $conn->prepare($sql_fert);
    $stmt_fert->bind_param("i", $farmer_id);
    $stmt_fert->execute();
    $res_fert = $stmt_fert->get_result()->fetch_assoc();
    $last_fert_date = $res_fert['last_fert_date'];
    $stmt_fert->close();

    if ($last_fert_date) {
        $last_fert = new DateTime($last_fert_date);
        $interval_fert = $today->diff($last_fert)->days;
        if ($interval_fert >= $fertilizer_threshold_days) {
            $msg = "ถึงเวลาต้องใส่ปุ๋ยในสวนเกษตรกร $farmer_name";
            addNotification($conn, $farmer_id, $owner_id, $msg);
        }
    } else {
        $msg = "ยังไม่เคยใส่ปุ๋ยในสวนเกษตรกร $farmer_name กรุณาตรวจสอบ";
        addNotification($conn, $farmer_id, $owner_id, $msg);
    }

    // เช็ควันล่าสุดเก็บน้ำยาง
    $sql_latex = "SELECT MAX(collection_date) AS last_latex_date FROM latex_collections WHERE farmer_id = ?";
    $stmt_latex = $conn->prepare($sql_latex);
    $stmt_latex->bind_param("i", $farmer_id);
    $stmt_latex->execute();
    $res_latex = $stmt_latex->get_result()->fetch_assoc();
    $last_latex_date = $res_latex['last_latex_date'];
    $stmt_latex->close();

    if ($last_latex_date) {
        $last_latex = new DateTime($last_latex_date);
        $interval_latex = $today->diff($last_latex)->days;
        if ($interval_latex >= $latex_collection_threshold_days) {
            $msg = "ถึงเวลาต้องเก็บน้ำยางในสวนเกษตรกร $farmer_name";
            addNotification($conn, $farmer_id, $owner_id, $msg);
        }
    } else {
        $msg = "ยังไม่เคยเก็บน้ำยางในสวนเกษตรกร $farmer_name กรุณาตรวจสอบ";
        addNotification($conn, $farmer_id, $owner_id, $msg);
    }
}

// ดึงแจ้งเตือนยังไม่อ่านมาแสดง
$sql_notifications = "SELECT n.notification_id, n.message, n.notification_date, f.farmer_name
                      FROM notifications n
                      JOIN farmers f ON n.farmer_id = f.farmer_id
                      WHERE n.owner_id = ? AND n.is_read = 0
                      ORDER BY n.notification_date DESC";

$stmt_notif = $conn->prepare($sql_notifications);
$stmt_notif->bind_param("i", $owner_id);
$stmt_notif->execute();
$notifications = $stmt_notif->get_result();

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แจ้งเตือนเจ้าของสวน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .notification {
            background: #f0f8ff;
            border: 1px solid #a2c4c9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .date {
            font-size: 0.85rem;
            color: #555;
        }

        .no-notifications {
            font-style: italic;
            color: #888;
        }
    </style>
</head>

<body>
    <h1>แจ้งเตือนเจ้าของสวน</h1>

    <?php if ($notifications->num_rows === 0): ?>
        <p class="no-notifications">ไม่มีแจ้งเตือนใหม่</p>
    <?php else: ?>
        <?php while ($row = $notifications->fetch_assoc()): ?>
            <div class="notification">
                <p><?php echo htmlspecialchars($row['message']); ?></p>
                <p class="date">วันที่แจ้งเตือน: <?php echo $row['notification_date']; ?><br>
                    เกษตรกร: <?php echo htmlspecialchars($row['farmer_name']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

</body>

</html>