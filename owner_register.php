<?php
include_once __DIR__ . '/includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_name = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $owner_email = mysqli_real_escape_string($conn, $_POST['owner_email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    // ... (เหมือนเดิม)
    if ($password !== $confirm_password) {
        $message = "รหัสผ่านไม่ตรงกัน";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM owners WHERE owner_email = '$owner_email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "อีเมลนี้ถูกใช้ไปแล้ว";
        } else {
            // ไม่เข้ารหัส รหัสผ่านเก็บแบบ plain text เลย
            $sql = "INSERT INTO owners (owner_name, owner_email, owner_password) VALUES ('$owner_name', '$owner_email', '$password')";
            if (mysqli_query($conn, $sql)) {
                header("Location: login.php?msg=registered_owner");
                exit();
            } else {
                $message = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
            }
        }
    }
    // ...

}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>สมัครเจ้าของสวน</title>
    <link rel="stylesheet" href="./css/login-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        form input {
            display: block;
            width: 100%;
            margin-bottom: 16px;
            padding: 12px 14px;
            font-size: 1rem;
            border: 2px solid #cbb18a;
            border-radius: 10px;
            background-color: #fffaf5;
            transition: all 0.3s ease;
        }

        form input:focus {
            outline: none;
            border-color: #a47454;
            box-shadow: 0 0 8px rgba(164, 116, 84, 0.4);
        }

        form button.card-btn {
            margin-top: 10px;
        }

        p {
            margin-top: 20px;
            font-size: 0.95rem;
        }

        p a {
            color: #a47454;
            font-weight: 600;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
            color: #7c5a2a;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1><i class="fas fa-user-plus"></i> สมัครเจ้าของสวน</h1>

        <?php if ($message): ?>
            <div class="message alert-error"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="owner_name" placeholder="ชื่อเจ้าของสวน" required />
            <input type="email" name="owner_email" placeholder="อีเมล" required />
            <input type="password" name="password" placeholder="รหัสผ่าน" required />
            <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required />
            <button type="submit" class="card-btn">
                <i class="fas fa-user-plus"></i> สมัครสมาชิก
            </button>
        </form>

        <p class="register-link">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a></p>
    </div>
</body>

</html>