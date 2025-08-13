<?php
$servername = "localhost";
$username = "root";
$password = "";  // ถ้าคุณตั้งรหัสผ่านให้ใส่ด้วย
$dbname = "rubber_management";  // ชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อ
$conn = mysqli_connect($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
