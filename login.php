<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($email) || empty($password) || empty($role)) {
        $message = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        if ($role === "owner") {
            $sql = "SELECT * FROM owners WHERE owner_email = '$email' LIMIT 1";
        } elseif ($role === "farmer") {
            $sql = "SELECT * FROM farmers WHERE farmer_email = '$email' LIMIT 1";
        } else {
            $message = "เลือกประเภทผู้ใช้งานไม่ถูกต้อง";
        }

        if (!$message) {
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);
                $stored_password = ($role === "owner") ? $user['owner_password'] : $user['farmer_password'];

                if ($password === $stored_password) {
                    if ($role === "owner") {
                        $_SESSION['owner_id'] = $user['owner_id'];
                        $_SESSION['owner_name'] = $user['owner_name'];
                        $_SESSION['role'] = 'owner';
                        header("Location: owner/owner_dashboard.php");
                        exit();
                    } elseif ($role === "farmer") {
                        if ($user['status'] !== 'approved') {
                            $_SESSION['farmer_id'] = $user['farmer_id'];
                            $_SESSION['farmer_name'] = $user['farmer_name'];
                            $_SESSION['role'] = 'farmer';
                            header("Location: farmer/farmer_wait_approval.php");
                            exit();
                        } else {
                            $_SESSION['farmer_id'] = $user['farmer_id'];
                            $_SESSION['farmer_name'] = $user['farmer_name'];
                            $_SESSION['owner_id'] = $user['owner_id'];
                            $_SESSION['role'] = 'farmer';
                            header("Location: farmer/farmer_dashboard.php");
                            exit();
                        }
                    }
                } else {
                    $message = "รหัสผ่านไม่ถูกต้อง";
                }
            } else {
                $message = "ไม่พบผู้ใช้งานในระบบ";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>เข้าสู่ระบบ | Rubber Plantation</title>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="./css/login-style.css">
        </head>

<body>
    <div class="login-container">
        <h2>เข้าสู่ระบบ</h2>

        <?php if ($message): ?>
            <div class="message alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="off">
            <label for="email">อีเมล</label>
            <input type="email" id="email" name="email" placeholder="กรอกอีเมล" required />

            <label for="password">รหัสผ่าน</label>
            <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่าน" required />

            <label for="role">ประเภทผู้ใช้งาน</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>-- เลือกประเภทผู้ใช้งาน --</option>
                <option value="owner">เจ้าของสวน</option>
                <option value="farmer">เกษตรกร</option>
            </select>

            <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
        </form>

        <div class="register-container">
            <h3>ยังไม่มีบัญชีผู้ใช้งาน?</h3>
            <div class="card-container">
                <a href="owner_register.php" class="card-btn">
                    <i class="fas fa-user-tie"></i>
                    <span>สมัครเจ้าของสวน</span>
                </a>
                <a href="farmer_register.php" class="card-btn">
                    <i class="fas fa-tractor"></i>
                    <span>สมัครเกษตรกร</span>
                </a>
            </div>
        </div>
    </div>
</body>

</html>