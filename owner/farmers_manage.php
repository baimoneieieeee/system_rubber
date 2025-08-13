<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id'])) {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];
$message = '';
$edit_farmer = null;

function email_exists($conn, $email, $exclude_id = null)
{
    if ($exclude_id) {
        $stmt = $conn->prepare("SELECT farmer_id FROM farmers WHERE farmer_email = ? AND farmer_id != ?");
        $stmt->bind_param("si", $email, $exclude_id);
    } else {
        $stmt = $conn->prepare("SELECT farmer_id FROM farmers WHERE farmer_email = ?");
        $stmt->bind_param("s", $email);
    }
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['farmer_name']);
    $email = trim($_POST['farmer_email']);
    $status = $_POST['status'] ?? 'pending';
    $is_approved = $status === 'approved' ? 1 : 0;

    if ($action === 'add') {
        $password_raw = $_POST['farmer_password'];
        if (email_exists($conn, $email)) {
            $message = "❌ อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น";
        } else {
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO farmers (farmer_name, farmer_email, farmer_password, owner_id, created_at, is_approved, status) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
            $stmt->bind_param("sssiss", $name, $email, $password, $owner_id, $is_approved, $status);
            if ($stmt->execute()) {
                $message = "✅ เพิ่มเกษตรกรเรียบร้อยแล้ว";
            } else {
                $message = "❌ เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
            }
            $stmt->close();
        }
    }

    if ($action === 'update') {
        $farmer_id = intval($_POST['farmer_id']);
        $password_raw = $_POST['farmer_password'];

        if (email_exists($conn, $email, $farmer_id)) {
            $message = "❌ อีเมลนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น";
        } else {
            if (!empty($password_raw)) {
                $password = password_hash($password_raw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE farmers SET farmer_name=?, farmer_email=?, farmer_password=?, is_approved=?, status=? WHERE farmer_id=? AND owner_id=?");
                $stmt->bind_param("sssisii", $name, $email, $password, $is_approved, $status, $farmer_id, $owner_id);
            } else {
                $stmt = $conn->prepare("UPDATE farmers SET farmer_name=?, farmer_email=?, is_approved=?, status=? WHERE farmer_id=? AND owner_id=?");
                $stmt->bind_param("ssisii", $name, $email, $is_approved, $status, $farmer_id, $owner_id);
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

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM farmers WHERE farmer_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $delete_id, $owner_id);
    if ($stmt->execute()) {
        $message = "✅ ลบเกษตรกรเรียบร้อยแล้ว";
    } else {
        $message = "❌ เกิดข้อผิดพลาดในการลบข้อมูล";
    }
    $stmt->close();
}

if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT farmer_id, farmer_name, farmer_email, status FROM farmers WHERE farmer_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $edit_id, $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_farmer = $result->fetch_assoc();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT farmer_id, farmer_name, farmer_email, status, is_approved, created_at FROM farmers WHERE owner_id = ? ORDER BY farmer_id DESC");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$farmers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>จัดการเกษตรกร</title>
    <!-- โหลด CSS จากไฟล์เดียวกับหน้าอื่น -->
    <link rel="stylesheet" href="../css/owner_dashboard.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script>
        function confirmDelete(name) {
            return confirm(`คุณแน่ใจว่าต้องการลบเกษตรกร: ${name} ?`);
        }

        function cancelEdit() {
            window.location.href = 'farmers_manage.php';
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <h2>เจ้าของสวน</h2>
        <nav>
            <a href="owner_dashboard.php"><i class="fas fa-home"></i> แดชบอร์ด</a>
            <a href="approve_farmer_action.php"><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</a>
            <a href="approved_farmers.php"><i class="fas fa-user-check"></i> เกษตรกรที่อนุมัติแล้ว</a>
            <!-- <a href="add_farmer.php" class="active"><i class="fas fa-user-plus"></i> เกษตรกรรออนุมัติ</a> -->
            <a href="farmers_manage.php" class="active"><i class="fas fa-users"></i> เพิ่มลบจัดการเกษตรกร</a>
            <a href="owners_manage.php"><i class="fas fa-users"></i>เพิ่มลบเจ้าของสวน</a>
            <a href="latex_collections_manage.php"><i class="fas fa-tint"></i> เพิ่มลบจัดการน้ำยาง</a>
            <a href="owner_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </div>

    <div class="main-content">
        <h1>จัดการเกษตรกร</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php if ($edit_farmer): ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="farmer_id" value="<?= $edit_farmer['farmer_id'] ?>" />
            <?php else: ?>
                <input type="hidden" name="action" value="add" />
            <?php endif; ?>

            <input type="text" name="farmer_name" placeholder="ชื่อเกษตรกร" required
                value="<?= $edit_farmer ? htmlspecialchars($edit_farmer['farmer_name']) : '' ?>" />
            <input type="email" name="farmer_email" placeholder="อีเมล" required
                value="<?= $edit_farmer ? htmlspecialchars($edit_farmer['farmer_email']) : '' ?>" />
            <input type="password" name="farmer_password" placeholder="<?= $edit_farmer ? 'กรอกเพื่อเปลี่ยนรหัสผ่าน (ไม่บังคับ)' : 'รหัสผ่าน' ?>" <?= $edit_farmer ? '' : 'required' ?> />

            <select name="status" required>
                <option value="pending" <?= $edit_farmer && $edit_farmer['status'] === 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                <option value="approved" <?= $edit_farmer && $edit_farmer['status'] === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
            </select>

            <button type="submit"><?= $edit_farmer ? 'อัปเดตเกษตรกร' : '➕ เพิ่มเกษตรกร' ?></button>
            <?php if ($edit_farmer): ?>
                <button type="button" class="btn-cancel" onclick="cancelEdit()">ยกเลิก</button>
            <?php endif; ?>
        </form>


        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ</th>
                    <th>อีเมล</th>
                    <th>วันที่สร้าง</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($farmers) === 0): ?>
                    <tr>
                        <td colspan="6">ยังไม่มีเกษตรกร</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($farmers as $farmer): ?>
                        <tr>
                            <td><?= $farmer['farmer_id'] ?></td>
                            <td><?= htmlspecialchars($farmer['farmer_name']) ?></td>
                            <td><?= htmlspecialchars($farmer['farmer_email']) ?></td>
                            <td><?= $farmer['created_at'] ?></td>
                            <td><?= htmlspecialchars($farmer['status']) ?></td>
                            <td>
                                <a href="?edit_id=<?= $farmer['farmer_id'] ?>">
                                    <button class="btn-edit" type="button">✏️ แก้ไข</button>
                                </a>
                                <a href="?delete_id=<?= $farmer['farmer_id'] ?>" onclick="return confirmDelete('<?= htmlspecialchars($farmer['farmer_name']) ?>')">
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