<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

// ดึงแจ้งเตือนของเกษตรกรในความดูแลเจ้าของสวนนี้
$sql = "SELECT n.notification_id, n.message, n.notification_date, n.is_read, f.farmer_name
        FROM notifications n
        JOIN farmers f ON n.farmer_id = f.farmer_id
        WHERE n.owner_id = $owner_id
        ORDER BY n.notification_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แจ้งเตือนดูแลสวน</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>แจ้งเตือนดูแลสวน</h1>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f0e6d2;">
                            <th>ชื่อเกษตรกร</th>
                            <th>ข้อความแจ้งเตือน</th>
                            <th>วันที่แจ้งเตือน</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td><?php echo htmlspecialchars($row['notification_date']); ?></td>
                                <td><?php echo $row['is_read'] ? 'อ่านแล้ว' : 'ยังไม่อ่าน'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>ไม่มีแจ้งเตือนในขณะนี้</p>
            <?php endif; ?>

            <p style="margin-top: 15px;">
                <a href="owner_dashboard.php">กลับสู่แดชบอร์ด</a>
            </p>
        </div>
    </div>
</body>

</html>