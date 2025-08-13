<?php
include_once __DIR__ . '/includes/db.php';
$owners = mysqli_query($conn, "SELECT owner_id, owner_name FROM owners ORDER BY owner_id ASC");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รหัสเจ้าของสวน</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <div class="box index-box">
            <h1>รายการรหัสเจ้าของสวน</h1>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f0e6d2;">
                        <th style="padding: 10px; border: 1px solid #ccc;">รหัส</th>
                        <th style="padding: 10px; border: 1px solid #ccc;">ชื่อเจ้าของสวน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($owners)): ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ccc;"><?php echo $row['owner_id']; ?></td>
                            <td style="padding: 10px; border: 1px solid #ccc;"><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>