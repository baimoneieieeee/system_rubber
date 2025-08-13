<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id'])) {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

// เพิ่มข้อมูลเมื่อฟอร์มถูกส่งมา
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = $_POST['farmer_id'];
    $collection_date = $_POST['collection_date'];
    $volume_liters = $_POST['volume_liters'];
    $collection_area = $_POST['collection_area'];
    $collection_zone = $_POST['collection_zone'];

    $stmt_insert = $conn->prepare("INSERT INTO latex_collections (owner_id, farmer_id, collection_date, volume_liters, collection_area, collection_zone, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt_insert->bind_param("iisdds", $owner_id, $farmer_id, $collection_date, $volume_liters, $collection_area, $collection_zone);
    $stmt_insert->execute();
}

// ดึงข้อมูลการเก็บน้ำยางพร้อมชื่อเจ้าของสวนและชื่อเกษตรกร
$stmt = $conn->prepare("
    SELECT lc.*, o.owner_name, f.farmer_name
    FROM latex_collections lc
    JOIN owners o ON lc.owner_id = o.owner_id
    JOIN farmers f ON lc.farmer_id = f.farmer_id
    WHERE lc.owner_id = ?
    ORDER BY lc.collection_date DESC
");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการข้อมูลการเก็บน้ำยาง</title>
    <link rel="stylesheet" href="../css/owner_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="wrapper">
        <!-- แถบเมนู -->
        <div class="sidebar">
            <h2>เจ้าของสวน</h2>
            <nav>
                <a href="owner_dashboard.php"><i class="fas fa-home"></i> แดชบอร์ด</a>
                <a href="approve_farmer_action.php"><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</a>
                <a href="approved_farmers.php"><i class="fas fa-user-check"></i> เกษตรกรที่อนุมัติแล้ว</a>
                <!-- <a href="add_farmer.php" class="active"><i class="fas fa-user-plus"></i> เกษตรกรรออนุมัติ</a> -->
                <a href="farmers_manage.php"><i class="fas fa-users"></i> เพิ่มลบจัดการเกษตรกร</a>
                <a href="owners_manage.php"><i class="fas fa-users"></i>เพิ่มลบเจ้าของสวน</a>
                <a href="latex_collections_manage.php" class="active"><i class="fas fa-tint"></i> เพิ่มลบจัดการน้ำยาง</a>
                <a href="owner_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
                <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- เนื้อหา -->
        <div class="main-content">
            <div class="box index-box">
                <h2>จัดการข้อมูลการเก็บน้ำยาง</h2>

                <!-- ฟอร์มเพิ่มข้อมูล -->
                <form method="post" class="form-add" style="margin-bottom: 2em;">
                    <div class="form-group">
                        <label for="farmer_id">รหัสเกษตรกร:</label>
                        <input type="number" name="farmer_id" required>
                    </div>
                    <div class="form-group">
                        <label for="collection_date">วันที่เก็บ:</label>
                        <input type="date" name="collection_date" required>
                    </div>
                    <div class="form-group">
                        <label for="volume_liters">ปริมาณ (ลิตร):</label>
                        <input type="number" step="0.01" name="volume_liters" required>
                    </div>
                    <div class="form-group">
                        <label for="collection_area">พื้นที่ (ไร่):</label>
                        <input type="number" step="0.1" name="collection_area" required>
                    </div>
                    <div class="form-group">
                        <label for="collection_zone">โซน:</label>
                        <input type="text" name="collection_zone" required>
                    </div>
                    <button type="submit" class="card-btn"><i class="fas fa-plus"></i> เพิ่มข้อมูล</button>
                </form>

                <!-- ตารางแสดงผล -->
                <table>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>รหัสเกษตรกร</th>
                            <th>ชื่อเกษตรกร</th>
                            <th>ชื่อเจ้าของสวน</th>
                            <th>วันที่เก็บ</th>
                            <th>ปริมาณ (ลิตร)</th>
                            <th>พื้นที่ (ไร่)</th>
                            <th>โซน</th>
                            <th>วันที่บันทึก</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['collection_id']) ?></td>
                                    <td><?= htmlspecialchars($row['farmer_id']) ?></td>
                                    <td><?= htmlspecialchars($row['farmer_name']) ?></td>
                                    <td><?= htmlspecialchars($row['owner_name']) ?></td>
                                    <td><?= htmlspecialchars($row['collection_date']) ?></td>
                                    <td><?= htmlspecialchars($row['volume_liters']) ?></td>
                                    <td><?= htmlspecialchars($row['collection_area']) ?></td>
                                    <td><?= htmlspecialchars($row['collection_zone']) ?></td>
                                    <td><?= htmlspecialchars($row['created_at'] ?? '-') ?></td>
                                    <td>
                                        <a href="latex_collections_edit.php?id=<?= htmlspecialchars($row['collection_id']) ?>" class="btn-edit">แก้ไข</a>
                                        <a href="latex_collections_delete.php?id=<?= htmlspecialchars($row['collection_id']) ?>" class="btn-delete" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?');">ลบ</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">ไม่พบข้อมูลการเก็บน้ำยาง</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>