<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];

if (isset($_POST['add_fertilizer'])) {
    $record_date = $_POST['record_date'];
    $fertilizer_type = $_POST['fertilizer_type'];
    $amount = floatval($_POST['amount']);

    $stmt = $conn->prepare("INSERT INTO fertilizer_records (farmer_id, record_date, fertilizer_type, amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $farmer_id, $record_date, $fertilizer_type, $amount);
    $stmt->execute();

    header("Location: add_fertilizer.php");
    exit();
}

$stmt = $conn->prepare("SELECT fertilizer_id, record_date, fertilizer_type, amount FROM fertilizer_records WHERE farmer_id = ? ORDER BY record_date DESC");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>จัดการปุ๋ย/ยา</title>
    <link rel="stylesheet" href="../css/farmer_dashboard.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

</head>

<body>
    <aside class="sidebar">
        <h2><i class="fas fa-tractor"></i> เกษตรกร</h2>
        <nav>
            <a href="farmer_dashboard.php"><i class="fas fa-home"></i> หน้าแรก</a>
            <a href="add_trees.php"><i class="fas fa-tree"></i> ต้นยาง</a>
            <a href="add_latex.php"><i class="fas fa-tint"></i> น้ำยาง</a>
            <a href="add_fertilizer.php" class="active"><i class="fas fa-flask"></i> ปุ๋ย/ยา</a>
            <a href="send_notifications.php"><i class="fas fa-bell"></i> แจ้งเตือน</a>
            <a href="farmer_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </aside>

    <main class="main-content">
        <h1>จัดการปุ๋ย/ยา</h1>

        <form method="post" action="">
            <label for="record_date">วันที่</label>
            <input type="date" id="record_date" name="record_date" required>

            <label for="fertilizer_type">ชนิดปุ๋ย/ยา</label>
            <input type="text" id="fertilizer_type" name="fertilizer_type" required>

            <label for="amount">จำนวน</label>
            <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>

            <button type="submit" name="add_fertilizer">เพิ่มปุ๋ย/ยา</button>
        </form>

        <h2>รายการปุ๋ย/ยา</h2>
        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>ชนิดปุ๋ย/ยา</th>
                    <th>จำนวน</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['record_date'] ?></td>
                            <td><?= htmlspecialchars($row['fertilizer_type']) ?></td>
                            <td><?= $row['amount'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">ยังไม่มีข้อมูลปุ๋ย/ยา</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>