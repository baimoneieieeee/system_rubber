<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

// ตรวจสอบว่าเป็นเจ้าของสวนที่ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['owner_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['owner_id'];
$owner_name = $_SESSION['owner_name'];

// ดึงข้อมูลเจ้าของสวน (ถ้าต้องการข้อมูลเพิ่มเติม)
$sql_owner = "SELECT * FROM owners WHERE owner_id = ?";
$stmt_owner = $conn->prepare($sql_owner);
$stmt_owner->bind_param("i", $owner_id);
$stmt_owner->execute();
$result_owner = $stmt_owner->get_result();
$owner = $result_owner->fetch_assoc();

// ดึงรายชื่อเกษตรกรที่ได้รับการอนุมัติของเจ้าของสวนนี้
$sql_farmers = "SELECT farmer_id, farmer_name, farmer_email FROM farmers WHERE owner_id = ? AND is_approved = 1 ORDER BY farmer_name ASC";
$stmt = $conn->prepare($sql_farmers);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result_farmers = $stmt->get_result();

// ดึงข้อมูลต้นยางของเจ้าของสวน เกษตรกรที่อนุมัติแล้ว
$sql_trees = "
    SELECT rt.tree_id, rt.farmer_id, rt.planting_date, rt.tree_count, f.farmer_name
    FROM rubber_trees rt
    JOIN farmers f ON rt.farmer_id = f.farmer_id
    WHERE rt.owner_id = ? AND f.is_approved = 1
    ORDER BY rt.planting_date DESC
    LIMIT 5
";
$stmt_trees = $conn->prepare($sql_trees);
$stmt_trees->bind_param("i", $owner_id);
$stmt_trees->execute();
$result_trees = $stmt_trees->get_result();

// ดึงข้อมูลการเก็บน้ำยาง 5 รายการล่าสุด ของเกษตรกรที่อนุมัติแล้ว
$sql_latex = "
    SELECT lc.collection_id, lc.collection_date, lc.volume_liters, lc.collection_area, f.farmer_name
    FROM latex_collections lc
    JOIN farmers f ON lc.farmer_id = f.farmer_id
    WHERE lc.owner_id = ? AND f.is_approved = 1
    ORDER BY lc.collection_date DESC
    LIMIT 5
";
$stmt_latex = $conn->prepare($sql_latex);
$stmt_latex->bind_param("i", $owner_id);
$stmt_latex->execute();
$result_latex = $stmt_latex->get_result();

// ดึงข้อมูลการใช้ปุ๋ย/ยา 5 รายการล่าสุด ของเกษตรกรที่อนุมัติแล้ว
$sql_fertilizer = "
    SELECT fr.fertilizer_id, fr.fertilizer_type, fr.amount, fr.record_date, f.farmer_name
    FROM fertilizer_records fr
    JOIN farmers f ON fr.farmer_id = f.farmer_id
    WHERE f.owner_id = ? AND f.is_approved = 1
    ORDER BY fr.record_date DESC
    LIMIT 5
";
$stmt_fertilizer = $conn->prepare($sql_fertilizer);
$stmt_fertilizer->bind_param("i", $owner_id);
$stmt_fertilizer->execute();
$result_fertilizer = $stmt_fertilizer->get_result();

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>เจ้าของสวน - แดชบอร์ด</title>
    <link rel="stylesheet" href="../css/owner_dashboard.css" />
    <!-- FontAwesome CDN สำหรับไอคอน -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <aside class="sidebar">
        <h2>เจ้าของสวน</h2>
        <nav>
            <a href="owner_dashboard.php" class="active"><i class="fas fa-home"></i> แดชบอร์ด</a>
            <a href="approve_farmer_action.php"><i class="fas fa-user-clock"></i> เกษตรกรรออนุมัติ</a>
            <a href="approved_farmers.php"><i class="fas fa-user-check"></i> เกษตรกรที่อนุมัติแล้ว</a>
            <!-- <a href="add_farmer.php" class="active"><i class="fas fa-user-plus"></i> เกษตรกรรออนุมัติ</a> -->
            <a href="farmers_manage.php"><i class="fas fa-users"></i> เพิ่มลบจัดการเกษตรกร</a>
            <a href="owners_manage.php"><i class="fas fa-users"></i>เพิ่มลบเจ้าของสวน</a>
            <a href="latex_collections_manage.php"><i class="fas fa-tint"></i> เพิ่มลบจัดการน้ำยาง</a>
            <a href="owner_edit_profile.php"><i class="fas fa-user-cog"></i> แก้ไขโปรไฟล์</a>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </nav>


    </aside>

    <main class="main-content">
        <div class="wrapper">
            <div class="box index-box">
                <h1>yak kin beer kub, <?php echo htmlspecialchars($owner_name); ?>!</h1>
                <p>นี่คือแดชบอร์ดเจ้าของสวนของคุณ</p>

                <h2>รายชื่อเกษตรกรในสวนของคุณ</h2>
                <?php if (isset($result_farmers) && $result_farmers->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ชื่อเกษตรกร</th>
                                <th>อีเมล</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($farmer = $result_farmers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($farmer['farmer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($farmer['farmer_email']); ?></td>
                                    <td class="actions">
                                        <a href="edit_farmer.php?farmer_id=<?php echo $farmer['farmer_id']; ?>" class="btn">แก้ไข</a>
                                        <a href="delete_farmer.php?farmer_id=<?php echo $farmer['farmer_id']; ?>" class="btn btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบเกษตรกรนี้?');">ลบ</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>ยังไม่มีเกษตรกรที่ได้รับการอนุมัติในสวนของคุณ</p>
                <?php endif; ?>

                <h2>ข้อมูลต้นยางล่าสุด</h2>
                <?php if (isset($result_trees) && $result_trees->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>รหัสต้นยาง</th>
                                <th>ชื่อเกษตรกร</th>
                                <th>วันที่ปลูก</th>
                                <th>จำนวนต้น</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($tree = $result_trees->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $tree['tree_id']; ?></td>
                                    <td><?php echo htmlspecialchars($tree['farmer_name']); ?></td>
                                    <td><?php echo $tree['planting_date']; ?></td>
                                    <td><?php echo $tree['tree_count']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>ยังไม่มีข้อมูลต้นยาง</p>
                <?php endif; ?>

                <h2>ข้อมูลการเก็บน้ำยางล่าสุด</h2>
                <?php if (isset($result_latex) && $result_latex->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>วันที่เก็บน้ำยาง</th>
                                <th>ปริมาณ (ลิตร)</th>
                                <th>พื้นที่เก็บ (ไร่)</th>
                                <th>ชื่อเกษตรกร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($latex = $result_latex->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $latex['collection_date']; ?></td>
                                    <td><?php echo $latex['volume_liters']; ?></td>
                                    <td><?php echo $latex['collection_area']; ?></td>
                                    <td><?php echo htmlspecialchars($latex['farmer_name']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>ยังไม่มีข้อมูลการเก็บน้ำยาง</p>
                <?php endif; ?>

                <h2>ข้อมูลการใช้ปุ๋ย/ยา 5 รายการล่าสุด</h2>
                <?php if (isset($result_fertilizer) && $result_fertilizer->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>ชนิดปุ๋ย/ยา</th>
                                <th>จำนวน</th>
                                <th>ชื่อเกษตรกร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fertilizer = $result_fertilizer->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $fertilizer['record_date']; ?></td>
                                    <td><?php echo htmlspecialchars($fertilizer['fertilizer_type']); ?></td>
                                    <td><?php echo $fertilizer['amount']; ?></td>
                                    <td><?php echo htmlspecialchars($fertilizer['farmer_name']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>ยังไม่มีข้อมูลการใช้ปุ๋ย/ยา</p>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <a href="owner_edit_profile.php" class="btn">แก้ไขโปรไฟล์</a>
                    <a href="add_farmer.php" class="btn">เพิ่มเกษตรกร</a>
                    <a href="../logout.php" class="btn btn-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </main>
</body>

</html>