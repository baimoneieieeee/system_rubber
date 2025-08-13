<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

// สรุปปริมาณน้ำยาง
$sql_latex = "SELECT SUM(CAST(volume_liters AS DECIMAL(10,2))) AS total_latex FROM latex_collections WHERE owner_id = '$owner_id'";
$res_latex = mysqli_query($conn, $sql_latex);
$total_latex = mysqli_fetch_assoc($res_latex)['total_latex'] ?? 0;

// สรุปปริมาณปุ๋ย/ยา
$sql_fertilizer = "SELECT SUM(CAST(amount AS DECIMAL(10,2))) AS total_fertilizer 
                   FROM fertilizer_records 
                   WHERE farmer_id IN (SELECT farmer_id FROM farmers WHERE owner_id = '$owner_id')";
$res_fertilizer = mysqli_query($conn, $sql_fertilizer);
$total_fertilizer = mysqli_fetch_assoc($res_fertilizer)['total_fertilizer'] ?? 0;

// นับจำนวนต้นยาง
$sql_trees = "SELECT COUNT(*) AS tree_count FROM rubber_trees WHERE owner_id = '$owner_id'";
$res_trees = mysqli_query($conn, $sql_trees);
$tree_count = mysqli_fetch_assoc($res_trees)['tree_count'] ?? 0;


?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>รายงานผลสรุปสวนยาง</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>รายงานผลสรุปสวนยางของคุณ</h1>

            <ul>
                <li>ปริมาณน้ำยางรวม: <strong><?php echo number_format($total_latex, 2); ?> ลิตร</strong></li>
                <li>ปริมาณปุ๋ย/ยาที่ใช้ทั้งหมด: <strong><?php echo number_format($total_fertilizer, 2); ?> หน่วย</strong></li>
                <li>จำนวนต้นยางทั้งหมด: <strong><?php echo $tree_count; ?> ต้น</strong></li>
            </ul>

            <a href="owner_dashboard.php" class="card-btn" style="margin-top: 20px;">กลับสู่แดชบอร์ด</a>
        </div>
    </div>
</body>

</html>