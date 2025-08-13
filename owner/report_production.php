<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

// ตรวจสอบสิทธิ์เจ้าของสวน
if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

// ดึงข้อมูลน้ำยางย้อนหลัง 12 เดือนล่าสุด
$sql = "SELECT YEAR(collection_date) AS year, MONTH(collection_date) AS month, 
        SUM(CAST(volume_liters AS DECIMAL(10,2))) AS total_volume
        FROM latex_collections
        WHERE owner_id = '$owner_id'
        GROUP BY year, month
        ORDER BY year DESC, month DESC
        LIMIT 12";


$result = mysqli_query($conn, $sql);

// เตรียมข้อมูลสำหรับแสดง
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>วิเคราะห์ผลผลิตน้ำยาง</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>วิเคราะห์ผลผลิตน้ำยางย้อนหลัง 12 เดือน</h1>
            <?php if (count($data) > 0): ?>
                <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f0e6d2;">
                            <th>ปี</th>
                            <th>เดือน</th>
                            <th>ปริมาณน้ำยางรวม (ลิตร)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['year']); ?></td>
                                <td><?php echo htmlspecialchars($d['month']); ?></td>
                                <td><?php echo number_format($d['total_volume'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>ยังไม่มีข้อมูลน้ำยางในระบบ</p>
            <?php endif; ?>
            <a href="owner_dashboard.php" class="card-btn" style="margin-top: 20px;">กลับสู่แดชบอร์ด</a>
        </div>
    </div>
</body>

</html>