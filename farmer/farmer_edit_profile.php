<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];

// ดึงข้อมูลเกษตรกรจากฐานข้อมูล
$sql = "SELECT farmer_name, farmer_email FROM farmers WHERE farmer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลเกษตรกร");
}

$farmer = $result->fetch_assoc();

$success_msg = "";
$error_msg = "";

// อัปเดตข้อมูลเมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['farmer_name']);
    $new_email = trim($_POST['farmer_email']);

    if (empty($new_name) || empty($new_email)) {
        $error_msg = "กรุณากรอกชื่อและอีเมลให้ครบถ้วน";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "รูปแบบอีเมลไม่ถูกต้อง";
    } else {
        $update_sql = "UPDATE farmers SET farmer_name = ?, farmer_email = ? WHERE farmer_id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("ssi", $new_name, $new_email, $farmer_id);

        if ($stmt_update->execute()) {
            $success_msg = "อัปเดตข้อมูลสำเร็จ";
            // อัปเดต session ชื่อ
            $_SESSION['farmer_name'] = $new_name;
            $farmer['farmer_name'] = $new_name;
            $farmer['farmer_email'] = $new_email;
        } else {
            $error_msg = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แก้ไขโปรไฟล์เกษตรกร</title>
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
            <a href="add_fertilizer.php"><i class="fas fa-flask"></i> ปุ๋ย/ยา</a>
            <a href="send_notifications.php"><i class="fas fa-bell"></i> แจ้งเตือน</a>
            <a href="farmer_edit_profile.php" class="active"x><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </aside>

    <main class="main-content">
        <h1>แก้ไขโปรไฟล์</h1>

        <?php if ($success_msg): ?>
            <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($success_msg); ?></p>
        <?php elseif ($error_msg): ?>
            <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error_msg); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="farmer_name">ชื่อ</label>
            <input type="text" id="farmer_name" name="farmer_name" value="<?php echo htmlspecialchars($farmer['farmer_name']); ?>" required />

            <label for="farmer_email">อีเมล</label>
            <input type="email" id="farmer_email" name="farmer_email" value="<?php echo htmlspecialchars($farmer['farmer_email']); ?>" required />

            <button type="submit"><i class="fas fa-save"></i> บันทึก</button>
        </form>
    </main>
</body>

</html>