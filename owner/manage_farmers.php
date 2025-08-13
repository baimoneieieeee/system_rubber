<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];

// อนุมัติเกษตรกร ถ้ามีการส่งค่า approve_id
if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    $sql = "UPDATE farmers SET is_approved = 1 WHERE farmer_id = ? AND owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $approve_id, $owner_id);
    $stmt->execute();
    header('Location: manage_farmers.php');
    exit();
}

// ดึงรายชื่อเกษตรกรที่ยังไม่อนุมัติ
$sql = "SELECT farmer_id, farmer_name, farmer_email FROM farmers WHERE owner_id = ? AND is_approved = 0 ORDER BY farmer_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>จัดการเกษตรกร</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>เกษตรกรที่รออนุมัติ</h1>
            <?php if ($result->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ชื่อเกษตรกร</th>
                            <th>อีเมล</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['farmer_email']); ?></td>
                                <td><a href="?approve_id=<?php echo $row['farmer_id']; ?>" onclick="return confirm('คุณต้องการอนุมัติเกษตรกรนี้ใช่หรือไม่?');">อนุมัติ</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>ไม่มีเกษตรกรที่รออนุมัติ</p>
            <?php endif; ?>

            <p><a href="owner_dashboard.php">กลับไปหน้าแดชบอร์ด</a></p>
        </div>
    </div>
</body>

</html>