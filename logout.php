<?php
session_start();

// ล้างข้อมูล session ทั้งหมด
$_SESSION = array();

// ถ้ามี cookie session ก็ลบ cookie ด้วย
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// ทำลาย session
session_destroy();

// ส่งกลับไปที่หน้า login
header('Location: index.php
');
exit();
