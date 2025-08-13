<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];

// ดึงแจ้งเตือนของเกษตรกรคนนี้
$stmt = $conn->prepare("SELECT notification_id, notification_date, message, is_read FROM notifications WHERE farmer_id = ? ORDER BY notification_date DESC");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แจ้งเตือนสำหรับเกษตรกร</title>
    <link rel="stylesheet" href="../css/farmer_dashboard.css" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />



    <script>
        function markAsRead(notificationId, button) {
            fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'notification_id=' + encodeURIComponent(notificationId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.disabled = true;
                        button.textContent = 'อ่านแล้ว';
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                    }
                })
                .catch(() => alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์'));
        }
    </script>
</head>

<body>
    <aside class="sidebar">
        <h2><i class="fas fa-tractor"></i> เกษตรกร</h2>
        <nav>
            <a href="farmer_dashboard.php"><i class="fas fa-home"></i> หน้าแรก</a>
            <a href="add_trees.php"><i class="fas fa-tree"></i> ต้นยาง</a>
            <a href="add_latex.php"><i class="fas fa-tint"></i> น้ำยาง</a>
            <a href="add_fertilizer.php"><i class="fas fa-flask"></i> ปุ๋ย/ยา</a>
            <a href="send_notifications.php" class="active"><i class="fas fa-bell"></i> แจ้งเตือน</a>
            <a href="farmer_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </aside>

    <div class="main-content">
        <h1>แจ้งเตือนสำหรับเกษตรกร</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>วันที่แจ้งเตือน</th>
                        <th>ข้อความแจ้งเตือน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['notification_date']) ?></td>
                            <td><?= htmlspecialchars($row['message']) ?></td>
                            <td>
                                <?php if ($row['is_read']): ?>
                                    อ่านแล้ว
                                <?php else: ?>
                                    <button onclick="markAsRead(<?= $row['notification_id'] ?>, this)">ทำเครื่องหมายว่าอ่านแล้ว</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>ไม่มีแจ้งเตือนในขณะนี้</p>
        <?php endif; ?>
    </div>
</body>

</html>