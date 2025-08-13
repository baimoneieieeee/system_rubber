<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];

// ฟังก์ชันดึงข้อมูล
function fetchData($conn, $sql, $types = "", $params = [])
{
    $stmt = $conn->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// ดึง owner_id ของเจ้าของสวนที่เกษตรกรนี้อยู่
$sql_owner = "SELECT owner_id FROM farmers WHERE farmer_id = ?";
$stmt_owner = $conn->prepare($sql_owner);
$stmt_owner->bind_param("i", $farmer_id);
$stmt_owner->execute();
$result_owner = $stmt_owner->get_result();
if ($result_owner->num_rows == 0) {
    die("ไม่พบเจ้าของสวนที่เกี่ยวข้อง");
}
$owner = $result_owner->fetch_assoc();
$owner_id = $owner['owner_id'];

// ดึงข้อมูลทั้งหมด (ไม่จำกัดจำนวน)
$sql_trees = "SELECT tree_id, planting_date, tree_count FROM rubber_trees WHERE farmer_id = ? ORDER BY planting_date DESC";
$result_trees = fetchData($conn, $sql_trees, "i", [$farmer_id]);

$sql_latex = "SELECT collection_id, collection_date, volume_liters, collection_area, collection_zone FROM latex_collections WHERE farmer_id = ? ORDER BY collection_date DESC";
$result_latex = fetchData($conn, $sql_latex, "i", [$farmer_id]);

$sql_fertilizer = "SELECT fertilizer_id, record_date, fertilizer_type, amount FROM fertilizer_records WHERE farmer_id = ? ORDER BY record_date DESC";
$result_fertilizer = fetchData($conn, $sql_fertilizer, "i", [$farmer_id]);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>แดชบอร์ดเกษตรกร</title>
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
            <a href="send_notifications.php" class="active"><i class="fas fa-bell"></i> แจ้งเตือน</a>
            <a href="farmer_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>

    </aside>

    <main class="main-content">
        <h1>สวัสดี, <?php echo htmlspecialchars($farmer_name); ?>!</h1>
        <p>นี่คือแดชบอร์ดเกษตรกรของคุณ</p>

        <!-- ข้อมูลต้นยางทั้งหมด -->
        <section id="trees">
            <h2>ข้อมูลต้นยางทั้งหมด</h2>
            <table>
                <thead>
                    <tr>
                        <th>รหัสต้นยาง</th>
                        <th>วันที่ปลูก</th>
                        <th>จำนวนต้น</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_trees->num_rows > 0): ?>
                        <?php while ($row = $result_trees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['tree_id']; ?></td>
                                <td><?php echo $row['planting_date']; ?></td>
                                <td><?php echo $row['tree_count']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">ยังไม่มีข้อมูลต้นยาง</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- ข้อมูลการเก็บน้ำยางทั้งหมด -->
        <section id="latex">
            <h2>ข้อมูลการเก็บน้ำยางทั้งหมด</h2>
            <table>
                <thead>
                    <tr>
                        <th>วันที่เก็บน้ำยาง</th>
                        <th>ปริมาณ (ลิตร)</th>
                        <th>พื้นที่เก็บ (ไร่)</th>
                        <th>โซน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_latex->num_rows > 0): ?>
                        <?php while ($row = $result_latex->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['collection_date']; ?></td>
                                <td><?php echo $row['volume_liters']; ?></td>
                                <td><?php echo $row['collection_area']; ?></td>
                                <td><?php echo htmlspecialchars($row['collection_zone'] ?: '-'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">ยังไม่มีข้อมูลการเก็บน้ำยาง</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- ข้อมูลการใช้ปุ๋ย/ยาทั้งหมด -->
        <section id="fertilizer">
            <h2>ข้อมูลการใช้ปุ๋ย/ยา ทั้งหมด</h2>
            <table>
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ชนิดปุ๋ย/ยา</th>
                        <th>จำนวน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_fertilizer->num_rows > 0): ?>
                        <?php while ($row = $result_fertilizer->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['record_date']; ?></td>
                                <td><?php echo htmlspecialchars($row['fertilizer_type']); ?></td>
                                <td><?php echo $row['amount']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">ยังไม่มีข้อมูลการใช้ปุ๋ย/ยา</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>



















































<!-- session_start();
include_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
header("Location: ../login.php");
exit();
}

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];


function fetchData($conn, $sql, $types = "", $params = [])
{
$stmt = $conn->prepare($sql);
if ($types && $params) {
$stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
return $result;
}


$sql_owner = "SELECT owner_id FROM farmers WHERE farmer_id = ?";
$stmt_owner = $conn->prepare($sql_owner);
$stmt_owner->bind_param("i", $farmer_id);
$stmt_owner->execute();
$result_owner = $stmt_owner->get_result();
if ($result_owner->num_rows == 0) {
die("ไม่พบเจ้าของสวนที่เกี่ยวข้อง");
}
$owner = $result_owner->fetch_assoc();
$owner_id = $owner['owner_id'];

if (isset($_POST['add_tree'])) {
$planting_date = $_POST['planting_date'];
$tree_count = intval($_POST['tree_count']);

$stmt = $conn->prepare("INSERT INTO rubber_trees (owner_id, farmer_id, planting_date, tree_count) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $owner_id, $farmer_id, $planting_date, $tree_count);
$stmt->execute();
header("Location: farmer_dashboard.php");
exit();
}


if (isset($_POST['add_latex'])) {
$collection_date = $_POST['collection_date'];
$volume_liters = floatval($_POST['volume_liters']);
$collection_area = floatval($_POST['collection_area']);
$collection_zone = !empty($_POST['collection_zone']) ? $_POST['collection_zone'] : null;

if ($collection_zone === null) {
$stmt = $conn->prepare("INSERT INTO latex_collections (owner_id, farmer_id, collection_date, volume_liters, collection_area) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issdd", $owner_id, $farmer_id, $collection_date, $volume_liters, $collection_area);
} else {
$stmt = $conn->prepare("INSERT INTO latex_collections (owner_id, farmer_id, collection_date, volume_liters, collection_area, collection_zone) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issdds", $owner_id, $farmer_id, $collection_date, $volume_liters, $collection_area, $collection_zone);
}

$stmt->execute();
header("Location: farmer_dashboard.php");
exit();
}



if (isset($_POST['add_fertilizer'])) {
$record_date = $_POST['record_date'];
$fertilizer_type = $_POST['fertilizer_type'];
$amount = floatval($_POST['amount']);

$stmt = $conn->prepare("INSERT INTO fertilizer_records (farmer_id, record_date, fertilizer_type, amount) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issd", $farmer_id, $record_date, $fertilizer_type, $amount);
$stmt->execute();
header("Location: farmer_dashboard.php");
exit();
}


$sql_trees = "SELECT tree_id, planting_date, tree_count FROM rubber_trees WHERE farmer_id = ? ORDER BY planting_date DESC LIMIT 5";
$result_trees = fetchData($conn, $sql_trees, "i", [$farmer_id]);


$sql_latex = "SELECT collection_id, collection_date, volume_liters, collection_area, collection_zone FROM latex_collections WHERE farmer_id = ? ORDER BY collection_date DESC LIMIT 5";
$result_latex = fetchData($conn, $sql_latex, "i", [$farmer_id]);

$sql_fertilizer = "SELECT fertilizer_id, record_date, fertilizer_type, amount FROM fertilizer_records WHERE farmer_id = ? ORDER BY record_date DESC LIMIT 5";
$result_fertilizer = fetchData($conn, $sql_fertilizer, "i", [$farmer_id]); -->





<!-- <!DOCTYPE html>
<html lang="th">
<link rel="stylesheet" href="../css/farmer-dashboard.css">

<head>
    <meta charset="UTF-8" />
    <title>แดชบอร์ดเกษตรกร</title>

</head>

<body>
    <div class="container">
        <h1>สวัสดี, <php echo htmlspecialchars($farmer_name); ?>!</h1>
        <p>นี่คือแดชบอร์ดเกษตรกรของคุณ</p>


        <h2>เพิ่มข้อมูลต้นยาง</h2>
        <form method="post" action="">
            <label for="planting_date">วันที่ปลูก</label>
            <input type="date" id="planting_date" name="planting_date" required>

            <label for="tree_count">จำนวนต้น</label>
            <input type="number" id="tree_count" name="tree_count" min="1" max="100000" required>

            <button type="submit" name="add_tree">เพิ่มข้อมูลต้นยาง</button>
        </form>


        <h2>ข้อมูลต้นยางล่าสุด</h2>
        <table>
            <thead>
                <tr>
                    <th>รหัสต้นยาง</th>
                    <th>วันที่ปลูก</th>
                    <th>จำนวนต้น</th>
                </tr>
            </thead>
            <tbody>
                <php if ($result_trees->num_rows > 0): ?>
                    <php while ($row = $result_trees->fetch_assoc()): ?>
                        <tr>
                            <td><php echo $row['tree_id']; ?></td>
                            <td><php echo $row['planting_date']; ?></td>
                            <td><php echo $row['tree_count']; ?></td>
                        </tr>
                    <php endwhile; ?>
                <php else: ?>
                    <tr>
                        <td colspan="3">ยังไม่มีข้อมูลต้นยาง</td>
                    </tr>
                <php endif; ?>
            </tbody>
        </table>


        <h2>เพิ่มข้อมูลการเก็บน้ำยาง</h2>
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

            <button type="submit" name="add_latex">เพิ่มข้อมูลเก็บน้ำยาง</button>
        </form>


        <h2>ข้อมูลการเก็บน้ำยางล่าสุด</h2>
        <table>
            <thead>
                <tr>
                    <th>วันที่เก็บน้ำยาง</th>
                    <th>ปริมาณ (ลิตร)</th>
                    <th>พื้นที่เก็บ (ไร่)</th>
                    <th>โซน</th>
                </tr>
            </thead>
            <tbody>
                <php if ($result_latex->num_rows > 0): ?>
                    <php while ($row = $result_latex->fetch_assoc()): ?>
                        <tr>
                            <td><php echo $row['collection_date']; ?></td>
                            <td><php echo $row['volume_liters']; ?></td>
                            <td><php echo $row['collection_area']; ?></td>
                            <td><php echo htmlspecialchars($row['collection_zone'] ?: '-'); ?></td>
                        </tr>
                    <php endwhile; ?>
                <php else: ?>
                    <tr>
                        <td colspan="4">ยังไม่มีข้อมูลการเก็บน้ำยาง</td>
                    </tr>
                <php endif; ?>
            </tbody>
        </table>


        <h2>เพิ่มข้อมูลการใช้ปุ๋ย/ยา</h2>
        <form method="post" action="">
            <label for="record_date">วันที่</label>
            <input type="date" id="record_date" name="record_date" required>

            <label for="fertilizer_type">ชนิดปุ๋ย/ยา</label>
            <input type="text" id="fertilizer_type" name="fertilizer_type" required>

            <label for="amount">จำนวน</label>
            <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>

            <button type="submit" name="add_fertilizer">เพิ่มข้อมูลปุ๋ย/ยา</button>
        </form>


        <h2>ข้อมูลการใช้ปุ๋ย/ยา 5 รายการล่าสุด</h2>
        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>ชนิดปุ๋ย/ยา</th>
                    <th>จำนวน</th>
                </tr>
            </thead>
            <tbody>
                <php if ($result_fertilizer->num_rows > 0): ?>
                    <php while ($row = $result_fertilizer->fetch_assoc()): ?>
                        <tr>
                            <td><php echo $row['record_date']; ?></td>
                            <td><php echo htmlspecialchars($row['fertilizer_type']); ?></td>
                            <td><php echo $row['amount']; ?></td>
                        </tr>
                    <php endwhile; ?>
                <php else: ?>
                    <tr>
                        <td colspan="3">ยังไม่มีข้อมูลการใช้ปุ๋ย/ยา</td>
                    </tr>
                <php endif; ?>
            </tbody>
        </table>

        <div class="btn-group">
            <a href="farmer_edit_profile.php" class="btn-link">แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="btn-link" style="color:#c0392b;">ออกจากระบบ</a>
        </div>
    </div>
</body>

</html> -->