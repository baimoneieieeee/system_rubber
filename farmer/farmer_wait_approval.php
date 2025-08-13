<?php
session_start();
if (!isset($_SESSION['farmer_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>รอเจ้าของสวนเคาะประตู</title>
    <style>
        body {
            background: #fffbe7;
            font-family: 'Kanit', sans-serif;
            text-align: center;
            padding: 80px 20px;
            color: #2c3e50;
        }

        h1 {
            font-size: 2.2em;
            margin-bottom: 15px;
            color: #d35400;
        }

        p {
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .funny-icon {
            font-size: 4em;
            animation: wiggle 1.2s infinite;
            display: inline-block;
        }

        @keyframes wiggle {
            0% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(4deg);
            }

            50% {
                transform: rotate(-4deg);
            }

            75% {
                transform: rotate(4deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 12px 24px;
            font-size: 1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>
    <div class="funny-icon">⏳</div>
    <h1>ใจเย็นพี่... ระบบกำลังรอเจ้าของสวนอนุมัติ!</h1>
    <p>ตอนนี้บัญชีของเพ่กำลังรอการอนุมัติ<br>เมื่อเจ้าของสวนกด "อนุมัติ" พี่ถึงจะใช้ได้งับ!</p>
    <!-- <img src="https://i.imgflip.com/4/4t0m5.jpg" alt="waiting meme">
    <h3>รอนานๆก็อาจจะบันทอลหัวใจ<h3> -->
    <!-- <li></li> -->
    <a href="../logout.php" class="logout-btn">ออกจากระบบไปหาอะไรทำก่อนค้าบอ้วง</a>
</body>

</html>