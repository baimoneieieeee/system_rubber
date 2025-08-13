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
    $collection_date = $_POST['collection_date'];
    $volume = floatval($_POST['volume']);

    if ($farmer_id <= 0 || empty($collection_date) || $volume <= 0) {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง";
    } else {
        $sql_insert = "INSERT INTO latex_collections (owner_id, farmer_id, collection_date, volume)
                       VALUES ($owner_id, $farmer_id, '$collection_date', $volume)";
        if (mysqli_query($conn, $sql_insert)) {
            $message = "บันทึกข้อมูลการเก็บน้ำยางเรียบร้อยแล้ว";
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
    <title>บันทึกการเก็บน้ำยาง</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>บันทึกการเก็บน้ำยาง</h1>

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

                <label for="collection_date">วันที่เก็บน้ำยาง</label><br />
                <input type="date" name="collection_date" id="collection_date" required /><br /><br />

                <label for="volume">ปริมาณน้ำยาง (กิโลกรัม/ลิตร)</label><br />
                <input type="number" name="volume" id="volume" step="0.01" min="0.01" required /><br /><br />

                <button type="submit" class="card-btn">บันทึกข้อมูล</button>
            </form>

            <p style="margin-top: 15px;">
                <a href="owner_dashboard.php">กลับสู่แดชบอร์ด</a>
            </p>
        </div>
    </div>
</body>

</html>