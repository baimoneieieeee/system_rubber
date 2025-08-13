<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

$sql = "SELECT * FROM farmers WHERE (owner_id IS NULL OR owner_id = 0) AND status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>เกษตรกรรออนุมัติ</title>
    <link rel="stylesheet" href="../css/owner_dashboard.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <div class="sidebar">
        <h2>เจ้าของสวน</h2>
        <nav>
            <a href="owner_dashboard.php"><i class="fas fa-home"></i> แดชบอร์ด</a>
            <a href="approve_farmer_action.php" class="active"><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</a>
            <a href="approved_farmers.php"><i class="fas fa-user-check"></i> เกษตรกรที่อนุมัติแล้ว</a>
            <!-- <a href="add_farmer.php" class="active"><i class="fas fa-user-plus"></i> เกษตรกรรออนุมัติ</a> -->
            <a href="farmers_manage.php"><i class="fas fa-users"></i> เพิ่มลบจัดการเกษตรกร</a>
            <a href="owners_manage.php"><i class="fas fa-users"></i>เพิ่มลบเจ้าของสวน</a>
            <a href="latex_collections_manage.php"><i class="fas fa-tint"></i> เพิ่มลบจัดการน้ำยาง</a>
            <a href="owner_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>


    </div>

    <div class="main-content">
        <h2><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อเกษตรกร</th>
                        <th>อีเมล</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($farmer = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($farmer['farmer_name']) ?></td>
                            <td><?= htmlspecialchars($farmer['farmer_email']) ?></td>
                            <td>
                                <a class="action-btn approve" href="approve_farmer_action.php?farmer_id=<?= $farmer['farmer_id'] ?>&action=approve">
                                    <i class="fas fa-check-circle"></i> อนุมัติ
                                </a>
                                <a class="action-btn reject" href="approve_farmer_action.php?farmer_id=<?= $farmer['farmer_id'] ?>&action=reject" onclick="return confirm('คุณแน่ใจที่จะปฏิเสธเกษตรกรนี้หรือไม่?');">
                                    <i class="fas fa-times-circle"></i> ปฏิเสธ
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-result">
                <i class="fas fa-info-circle"></i> ไม่มีเกษตรกรรออนุมัติในขณะนี้
            </div>
        <?php endif; ?>
    </div>
</body>

</html>