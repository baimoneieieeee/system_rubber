<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];

if (isset($_POST['add_latex'])) {
    $collection_date = $_POST['collection_date'];
    $volume_liters = floatval($_POST['volume_liters']);
    $collection_area = floatval($_POST['collection_area']);
    $collection_zone = !empty($_POST['collection_zone']) ? $_POST['collection_zone'] : null;

    // ดึง owner_id
    $stmt_owner = $conn->prepare("SELECT owner_id FROM farmers WHERE farmer_id = ?");
    $stmt_owner->bind_param("i", $farmer_id);
    $stmt_owner->execute();
    $result_owner = $stmt_owner->get_result();
    $owner = $result_owner->fetch_assoc();
    $owner_id = $owner['owner_id'];

    if ($collection_zone === null) {
        $stmt = $conn->prepare("INSERT INTO latex_collections (owner_id, farmer_id, collection_date, volume_liters, collection_area) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdd", $owner_id, $farmer_id, $collection_date, $volume_liters, $collection_area);
    } else {
        $stmt = $conn->prepare("INSERT INTO latex_collections (owner_id, farmer_id, collection_date, volume_liters, collection_area, collection_zone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdds", $owner_id, $farmer_id, $collection_date, $volume_liters, $collection_area, $collection_zone);
    }
    $stmt->execute();

    header("Location: add_latex.php");
    exit();
}

$stmt = $conn->prepare("SELECT collection_id, collection_date, volume_liters, collection_area, collection_zone FROM latex_collections WHERE farmer_id = ? ORDER BY collection_date DESC");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>จัดการน้ำยาง</title>
    <link rel="stylesheet" href="../css/farmer_dashboard.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

</head>

<body>
    <aside class="sidebar">
        <h2><i class="fas fa-tractor"></i> เกษตรกร</h2>
        <nav>
            <a href="farmer_dashboard.php"><i class="fas fa-home"></i> หน้าแรก</a>
            <a href="add_trees.php"><i class="fas fa-tree"></i> ต้นยาง</a>
            <a href="add_latex.php" class="active"><i class="fas fa-tint"></i> น้ำยาง</a>
            <a href="add_fertilizer.php"><i class="fas fa-flask"></i> ปุ๋ย/ยา</a>
            <a href="send_notifications.php"><i class="fas fa-bell"></i> แจ้งเตือน</a>
            <a href="farmer_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </aside>

    <main class="main-content">
        <h1>จัดการน้ำยาง</h1>

        <form method="post" action="">
            <label for="collection_date">วันที่เก็บน้ำยาง</label>
            <input type="date" id="collection_date" name="collection_date" required>

            <label for="volume_liters">ปริมาณ (ลิตร)</label>
            <input type="number" id="volume_liters" name="volume_liters" min="0.01" step="0.01" required>

            <label for="collection_area">พื้นที่เก็บ (ไร่)</label>
            <input type="number" id="collection_area" name="collection_area" min="0.01" step="0.01" required>

            <label for="collection_zone">โซน (ไม่บังคับ)</label>
            <select id="collection_zone" name="collection_zone">
                <option value="">-- ไม่ระบุ --</option>
                <option value="10">โซน 10</option>
                <option value="11">โซน 11</option>
                <option value="12">โซน 12</option>
            </select>

            <button type="submit" name="add_latex">เพิ่มน้ำยาง</button>
        </form>

        <h2>รายการเก็บน้ำยาง</h2>
        <table>
            <thead>
                <tr>
                    <th>วันที่เก็บ</th>
                    <th>ปริมาณ (ลิตร)</th>
                    <th>พื้นที่เก็บ (ไร่)</th>
                    <th>โซน</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['collection_date'] ?></td>
                            <td><?= $row['volume_liters'] ?></td>
                            <td><?= $row['collection_area'] ?></td>
                            <td><?= htmlspecialchars($row['collection_zone'] ?: '-') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">ยังไม่มีข้อมูลน้ำยาง</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>