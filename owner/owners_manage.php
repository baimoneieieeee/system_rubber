<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id'])) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$edit_owner = null;

// ฟังก์ชันตรวจสอบ email ซ้ำ (ยกเว้นตัวเองตอนแก้ไข)
function email_exists($conn, $email, $exclude_id = null)
{
    if ($exclude_id) {
        $stmt = $conn->prepare("SELECT owner_id FROM owners WHERE owner_email = ? AND owner_id != ?");
        $stmt->bind_param("si", $email, $exclude_id);
    } else {
        $stmt = $conn->prepare("SELECT owner_id FROM owners WHERE owner_email = ?");
        $stmt->bind_param("s", $email);
    }
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['owner_name']);
    $email = trim($_POST['owner_email']);

    if ($action === 'add') {
        $password_raw = $_POST['owner_password'];
        if (email_exists($conn, $email)) {
            $message = "❌ อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น";
        } else {
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO owners (owner_name, owner_email, owner_password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $name, $email, $password);
            if ($stmt->execute()) {
                $message = "✅ เพิ่มเจ้าของสวนเรียบร้อยแล้ว";
            } else {
                $message = "❌ เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
            }
            $stmt->close();
        }
    }

    if ($action === 'update') {
        $owner_id = intval($_POST['owner_id']);
        $password_raw = $_POST['owner_password'];

        if (email_exists($conn, $email, $owner_id)) {
            $message = "❌ อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น";
        } else {
            if (!empty($password_raw)) {
                $password = password_hash($password_raw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE owners SET owner_name=?, owner_email=?, owner_password=? WHERE owner_id=?");
                $stmt->bind_param("sssi", $name, $email, $password, $owner_id);
            } else {
                $stmt = $conn->prepare("UPDATE owners SET owner_name=?, owner_email=? WHERE owner_id=?");
                $stmt->bind_param("ssi", $name, $email, $owner_id);
            }
            if ($stmt->execute()) {
                $message = "✅ อัปเดตข้อมูลเรียบร้อยแล้ว";
            } else {
                $message = "❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
            }
            $stmt->close();
        }
    }
}

// ลบ
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM owners WHERE owner_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "✅ ลบเจ้าของสวนเรียบร้อยแล้ว";
    } else {
        $message = "❌ เกิดข้อผิดพลาดในการลบข้อมูล";
    }
    $stmt->close();
}

// โหลดข้อมูลเจ้าของสวนที่จะแก้ไข (ถ้ามี)
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT owner_id, owner_name, owner_email FROM owners WHERE owner_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_owner = $result->fetch_assoc();
    $stmt->close();
}

// ดึงข้อมูลทั้งหมด
$result = $conn->query("SELECT owner_id, owner_name, owner_email, created_at FROM owners ORDER BY owner_id DESC");
$owners = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>จัดการเจ้าของสวน (Owners รายชื่อ)</title>
    <link rel="stylesheet" href="../css/owner_dashboard.css" />
</head>

<body>
    <div class="sidebar">
        <h2>เจ้าของสวน</h2>
        <nav>
            <a href="owner_dashboard.php"><i class="fas fa-home"></i> แดชบอร์ด</a>
            <a href="approve_farmer_action.php"><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</a>
            <a href="approved_farmers.php"><i class="fas fa-user-check"></i> เกษตรกรที่อนุมัติแล้ว</a>
            <!-- <a href="add_farmer.php" class="active"><i class="fas fa-user-plus"></i> เกษตรกรรออนุมัติ</a> -->
            <a href="farmers_manage.php"><i class="fas fa-users"></i> เพิ่มลบจัดการเกษตรกร</a>
            <a href="owners_manage.php" class="active"><i class="fas fa-users"></i>เพิ่มลบเจ้าของสวน</a>
            <a href="latex_collections_manage.php"><i class="fas fa-tint"></i> เพิ่มลบจัดการน้ำยาง</a>
            <a href="owner_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>

    </div>

    <div class="main-content">
        <h2><i class="fas fa-users"></i> จัดการเจ้าของสวน</h2>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php if ($edit_owner): ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="owner_id" value="<?= $edit_owner['owner_id'] ?>" />
            <?php else: ?>
                <input type="hidden" name="action" value="add" />
            <?php endif; ?>

            <input type="text" name="owner_name" placeholder="ชื่อเจ้าของสวน" required
                value="<?= $edit_owner ? htmlspecialchars($edit_owner['owner_name']) : '' ?>" />
            <input type="email" name="owner_email" placeholder="อีเมล" required
                value="<?= $edit_owner ? htmlspecialchars($edit_owner['owner_email']) : '' ?>" />
            <input type="password" name="owner_password" placeholder="<?= $edit_owner ? 'กรอกเพื่อเปลี่ยนรหัสผ่าน (ไม่บังคับ)' : 'รหัสผ่าน' ?>" <?= $edit_owner ? '' : 'required' ?> />

            <button type="submit"><?= $edit_owner ? 'อัปเดตเจ้าของสวน' : '➕ เพิ่มเจ้าของสวน' ?></button>
            <?php if ($edit_owner): ?>
                <button type="button" class="btn-cancel" onclick="window.location.href='owners_manage.php'">ยกเลิก</button>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อเจ้าของสวน</th>
                    <th>อีเมล</th>
                    <th>วันที่สร้าง</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($owners) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">ยังไม่มีเจ้าของสวน</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($owners as $owner): ?>
                        <tr>
                            <td><?= $owner['owner_id'] ?></td>
                            <td><?= htmlspecialchars($owner['owner_name']) ?></td>
                            <td><?= htmlspecialchars($owner['owner_email']) ?></td>
                            <td><?= $owner['created_at'] ?></td>
                            <td>
                                <a href="?edit_id=<?= $owner['owner_id'] ?>">
                                    <button class="btn-edit" type="button">✏️ แก้ไข</button>
                                </a>
                                <a href="?delete_id=<?= $owner['owner_id'] ?>" onclick="return confirm('คุณแน่ใจว่าต้องการลบเจ้าของสวน: <?= htmlspecialchars($owner['owner_name']) ?>?')">
                                    <button class="btn-delete" type="button">🗑️ ลบ</button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>