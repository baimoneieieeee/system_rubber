<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];
$owner_name = $_SESSION['owner_name'];

// ดึงรายชื่อเกษตรกรของเจ้าของสวนนี้
$sql = "SELECT farmer_name, farmer_email FROM farmers WHERE owner_id = $owner_id ORDER BY farmer_name ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายชื่อเกษตรกรของคุณ</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>เกษตรกรในสวนของคุณ<br><small>เจ้าของสวน: <?php echo htmlspecialchars($owner_name); ?></small></h1>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f0e6d2;">
                            <th style="padding: 10px; border: 1px solid #ccc;">ชื่อเกษตรกร</th>
                            <th style="padding: 10px; border: 1px solid #ccc;">อีเมล</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                                <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($row['farmer_email']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin-top: 20px; color: #a74f3c;">ยังไม่มีเกษตรกรในสวนของคุณ</p>
            <?php endif; ?>

            <div class="register-link" style="margin-top: 20px;">
                <a href="owner_dashboard.php">← กลับไปหน้าหลักเจ้าของสวน</a>
            </div>
        </div>
    </div>
</body>

</html>