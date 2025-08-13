<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];
$message = "";

// ดึงรายชื่อเกษตรกรที่อยู่ในความดูแลเจ้าของสวนนี้
$sql_farmers = "SELECT farmer_id, farmer_name FROM farmers WHERE owner_id = $owner_id ORDER BY farmer_name ASC";
$result_farmers = mysqli_query($conn, $sql_farmers);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = intval($_POST['farmer_id']);
    $usage_date = $_POST['usage_date'];
    $fertilizer_name = mysqli_real_escape_string($conn, $_POST['fertilizer_name']);
    $quantity = floatval($_POST['quantity']);

    if ($farmer_id <= 0 || empty($usage_date) || empty($fertilizer_name) || $quantity <= 0) {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง";
    } else {
        $sql_insert = "INSERT INTO fertilizer_usages (owner_id, farmer_id, usage_date, fertilizer_name, quantity)
                       VALUES ($owner_id, $farmer_id, '$usage_date', '$fertilizer_name', $quantity)";
        if (mysqli_query($conn, $sql_insert)) {
            $message = "บันทึกข้อมูลการใช้ปุ๋ย/ยาเรียบร้อยแล้ว";
        } else {
            $message = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>บันทึกการใช้ปุ๋ย/ยา</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>บันทึกการใช้ปุ๋ย/ยา</h1>

            <?php if ($message): ?>
                <div class="alert-error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <label for="farmer_id">เลือกเกษตรกร</label><br />
                <select name="farmer_id" id="farmer_id" required>
                    <option value="">-- เลือกเกษตรกร --</option>
                    <?php while ($farmer = mysqli_fetch_assoc($result_farmers)): ?>
                        <option value="<?php echo $farmer['farmer_id']; ?>">
                            <?php echo htmlspecialchars($farmer['farmer_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select><br /><br />

                <label for="usage_date">วันที่ใช้</label><br />
                <input type="date" name="usage_date" id="usage_date" required /><br /><br />

                <label for="fertilizer_name">ชนิดปุ๋ย/ยา</label><br />
                <input type="text" name="fertilizer_name" id="fertilizer_name" placeholder="ชื่อปุ๋ยหรือยา" required /><br /><br />

                <label for="quantity">ปริมาณที่ใช้ (กิโลกรัม/ลิตร)</label><br />
                <input type="number" name="quantity" id="quantity" step="0.01" min="0.01" required /><br /><br />

                <button type="submit" class="card-btn">บันทึกข้อมูล</button>
            </form>

            <p style="margin-top: 15px;">
                <a href="owner_dashboard.php">กลับสู่แดชบอร์ด</a>
            </p>
        </div>
    </div>
</body>

</html>