<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id'])) {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];
$message = '';
$edit_record = null;

// ดึงรายชื่อเกษตรกรของเจ้าของสวน (ใช้สำหรับ select option)
$stmt = $conn->prepare("SELECT farmer_id, farmer_name FROM farmers WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$farmers_result = $stmt->get_result();
$farmers = $farmers_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ฟังก์ชันเช็คค่า input ง่าย ๆ (เพิ่มได้ตามต้องการ)
function sanitize_input($data)
{
    return htmlspecialchars(trim($data));
}

// HANDLE FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $farmer_id = intval($_POST['farmer_id'] ?? 0);
    $record_date = $_POST['record_date'] ?? '';
    $fertilizer_type = sanitize_input($_POST['fertilizer_type'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO fertilizer_records (farmer_id, record_date, fertilizer_type, amount, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("issd", $farmer_id, $record_date, $fertilizer_type, $amount);
        if ($stmt->execute()) {
            $message = "✅ เพิ่มข้อมูลบันทึกปุ๋ยเรียบร้อยแล้ว";
        } else {
            $message = "❌ เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
        }
        $stmt->close();
    }

    if ($action === 'update') {
        $fertilizer_id = intval($_POST['fertilizer_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE fertilizer_records SET farmer_id=?, record_date=?, fertilizer_type=?, amount=? WHERE fertilizer_id=?");
        $stmt->bind_param("issdi", $farmer_id, $record_date, $fertilizer_type, $amount, $fertilizer_id);
        if ($stmt->execute()) {
            $message = "✅ อัปเดตข้อมูลเรียบร้อยแล้ว";
        } else {
            $message = "❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
        $stmt->close();
    }
}

// ลบ
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM fertilizer_records WHERE fertilizer_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "✅ ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        $message = "❌ เกิดข้อผิดพลาดในการลบข้อมูล";
    }
    $stmt->close();
}

// โหลดข้อมูลที่จะแก้ไข
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM fertilizer_records WHERE fertilizer_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_record = $result->fetch_assoc();
    $stmt->close();
}

// ดึงข้อมูลทั้งหมดเพื่อแสดงตาราง (JOIN กับ farmers เพื่อแสดงชื่อเกษตรกร)
$sql = "SELECT fr.*, f.farmer_name 
        FROM fertilizer_records fr
        LEFT JOIN farmers f ON fr.farmer_id = f.farmer_id
        WHERE f.owner_id = ?
        ORDER BY fr.fertilizer_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>จัดการบันทึกปุ๋ย</title>
    <link rel="stylesheet" href="../css/owner_dashboard.css" />
    <style>
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 15px;
            align-items: center;
            background-color: #fff8f3;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            max-width: 800px;
            font-family: 'Kanit', sans-serif;
        }

        form select,
        form input[type="date"],
        form input[type="text"],
        form input[type="number"] {
            flex: 1 1 180px;
            min-width: 180px;
            padding: 8px 10px;
            font-size: 1rem;
            border: 1px solid #b49c74;
            border-radius: 5px;
            box-sizing: border-box;
        }

        form button {
            flex: 0 0 auto;
            background-color: #a47454;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #8c5d3f;
        }

        form .btn-cancel {
            background-color: #777;
            margin-left: 10px;
        }

        form .btn-cancel:hover {
            background-color: #555;
        }
    </style>
    <script>
        function confirmDelete(fertilizerType) {
            return confirm(`คุณแน่ใจว่าต้องการลบข้อมูลปุ๋ย: ${fertilizerType} ?`);
        }

        function cancelEdit() {
            window.location.href = 'fertilizer_records_manage.php';
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <h2>เจ้าของสวน</h2>
        <nav>
            <a href="owner_dashboard.php"><i class="fas fa-home"></i> แดชบอร์ดอย่าลืมเพิ่มจักการ</a>
            <a href="fertilizer_records_manage.php" class="active"><i class="fas fa-leaf"></i> เพิ่มลบบันทึกปุ๋ย</a>
            <a href="pending_farmers.php"><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</a>
            <a href="approved_farmers.php"><i class="fas fa-user-check"></i> เกษตรกรที่อนุมัติแล้ว</a>
            <a href="add_farmer.php"><i class="fas fa-user-plus"></i> เพิ่มเกษตรกรแยกเพิ่มเข้าสวนนะ</a>
            <a href="farmers_manage.php" <i class="fas fa-users"></i> จัดการเกษตรกร</a>
            <a href="owners_manage.php" class="active"><i class="fas fa-users"></i> เพิ่มลบเจ้าของสวน</a>
            <a href="owner_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>
    </div>

    <div class="main-content">
        <h1>จัดการบันทึกปุ๋ย</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php if ($edit_record): ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="fertilizer_id" value="<?= $edit_record['fertilizer_id'] ?>" />
            <?php else: ?>
                <input type="hidden" name="action" value="add" />
            <?php endif; ?>

            <select name="farmer_id" required>
                <option value="">-- เลือกเกษตรกร --</option>
                <?php foreach ($farmers as $farmer): ?>
                    <option value="<?= $farmer['farmer_id'] ?>" <?= $edit_record && $edit_record['farmer_id'] == $farmer['farmer_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($farmer['farmer_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="record_date" required
                value="<?= $edit_record ? $edit_record['record_date'] : '' ?>" />

            <input type="text" name="fertilizer_type" placeholder="ชนิดปุ๋ย/ยา" required
                value="<?= $edit_record ? htmlspecialchars($edit_record['fertilizer_type']) : '' ?>" />

            <input type="number" step="0.01" min="0" name="amount" placeholder="ปริมาณ (กิโลกรัม)" required
                value="<?= $edit_record ? $edit_record['amount'] : '' ?>" />

            <button type="submit"><?= $edit_record ? 'อัปเดตข้อมูล' : '➕ เพิ่มบันทึก' ?></button>
            <?php if ($edit_record): ?>
                <button type="button" class="btn-cancel" onclick="cancelEdit()">ยกเลิก</button>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อเกษตรกร</th>
                    <th>วันที่</th>
                    <th>ชนิดปุ๋ย/ยา</th>
                    <th>ปริมาณ (กิโลกรัม)</th>
                    <th>วันที่สร้าง</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">ไม่มีข้อมูลบันทึกปุ๋ย</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= $row['fertilizer_id'] ?></td>
                            <td><?= htmlspecialchars($row['farmer_name']) ?></td>
                            <td><?= $row['record_date'] ?></td>
                            <td><?= htmlspecialchars($row['fertilizer_type']) ?></td>
                            <td><?= number_format($row['amount'], 2) ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="?edit_id=<?= $row['fertilizer_id'] ?>">
                                    <button class="btn-edit" type="button">✏️ แก้ไข</button>
                                </a>
                                <a href="?delete_id=<?= $row['fertilizer_id'] ?>"
                                    onclick="return confirmDelete('<?= htmlspecialchars($row['fertilizer_type']) ?>')">
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