<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];

if (isset($_POST['add_tree'])) {
    $planting_date = $_POST['planting_date'];
    $tree_count = intval($_POST['tree_count']);

    // ดึง owner_id
    $stmt_owner = $conn->prepare("SELECT owner_id FROM farmers WHERE farmer_id = ?");
    $stmt_owner->bind_param("i", $farmer_id);
    $stmt_owner->execute();
    $result_owner = $stmt_owner->get_result();
    $owner = $result_owner->fetch_assoc();
    $owner_id = $owner['owner_id'];

    $stmt = $conn->prepare("INSERT INTO rubber_trees (owner_id, farmer_id, planting_date, tree_count) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $owner_id, $farmer_id, $planting_date, $tree_count);
    $stmt->execute();

    header("Location: add_trees.php");
    exit();
}

// ดึงข้อมูลต้นยาง
$stmt = $conn->prepare("SELECT tree_id, planting_date, tree_count FROM rubber_trees WHERE farmer_id = ? ORDER BY planting_date DESC");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>จัดการต้นยาง</title>
    <link rel="stylesheet" href="../css/farmer_dashboard.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

</head>

<body>
    <aside class="sidebar">
        <h2><i class="fas fa-tractor"></i> เกษตรกร</h2>
        <nav>
            <a href="farmer_dashboard.php"><i class="fas fa-home"></i> หน้าแรก</a>
            <a href="add_trees.php" class="active"><i class="fas fa-tree"></i> ต้นยาง</a>
            <a href="add_latex.php"><i class="fas fa-tint"></i> น้ำยาง</a>
            <a href="add_fertilizer.php"><i class="fas fa-flask"></i> ปุ๋ย/ยา</a>
            <a href="notifications.php"><i class="fas fa-bell"></i> แจ้งเตือน</a>
            <a href="farmer_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </aside>

    <main class="main-content">
        <h1>จัดการต้นยาง</h1>

        <form method="post" action="">
            <label for="planting_date">วันที่ปลูก</label>
            <input type="date" id="planting_date" name="planting_date" required>

            <label for="tree_count">จำนวนต้น</label>
            <input type="number" id="tree_count" name="tree_count" min="1" required>

            <button type="submit" name="add_tree">เพิ่มต้นยาง</button>
        </form>

        <h2>รายการต้นยาง</h2>
        <table>
            <thead>
                <tr>
                    <th>รหัสต้นยาง</th>
                    <th>วันที่ปลูก</th>
                    <th>จำนวนต้น</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['tree_id'] ?></td>
                            <td><?= $row['planting_date'] ?></td>
                            <td><?= $row['tree_count'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">ยังไม่มีข้อมูลต้นยาง</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>