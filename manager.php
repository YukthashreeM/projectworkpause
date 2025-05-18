<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            color: #333;
        }

        header {
            background: linear-gradient(to right, #2f80ed, #56ccf2);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            flex-wrap: wrap;
        }

        header h1 {
            font-size: 26px;
        }

        .user-info {
            font-size: 14px;
            margin-top: 5px;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .container {
            padding: 40px 30px;
        }

        .welcome {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
        }

        .welcome h2 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        .welcome p {
            font-size: 16px;
            color: #555;
        }

        nav {
            text-align: center;
            margin-top: 40px;
        }

        nav a {
            margin: 8px;
            padding: 12px 24px;
            background-color: #2f80ed;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        nav a:hover {
            background-color: #1c5db8;
        }
    </style>
</head>
<body>

<header>
    <div>
        <h1>Manager Dashboard</h1>
        <div class="user-info">Logged in as: <strong><?= $username ?></strong></div>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="container">
    <div class="welcome">
        <h2>Welcome, <?= $username ?>!</h2>
        <p>Use the navigation below to manage attendance, leave requests, view reports, and more.</p>
    </div>

    <nav>
        <a href="manager.php">Dashboard</a>
        <a href="attendance.php">Attendance</a>
        <a href="apply_leave.php">Apply Leave</a>
        <a href="reports.php">View Reports</a>
        <a href="cab.php">Cab</a>
        <a href="settings.php">Settings</a>
        <a href="payslip.php">Payslip</a>
        <a href="emp_appraisal.php">Appraisal</a>
    </nav>
</div>

</body>
</html>