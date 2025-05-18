<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employee') {
    header("Location: login.php");
    exit();
}

require 'db.php';

$username = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role'] ?? 'Employee');
$email = 'Not found';
$empId = 'N/A';

try {
    $stmt = $pdo->prepare("SELECT email, id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $email = htmlspecialchars($user['email']);
        $empId = 'EMP' . str_pad($user['id'], 5, '0', STR_PAD_LEFT);
    }
} catch (PDOException $e) {
    $email = 'Error fetching email';
}
?>

<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c3e50, #34495e);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
        }

        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .user-info p {
            font-size: 14px;
            color: #bbb;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
            font-size: 16px;
        }

        .nav-link i {
            margin-right: 12px;
        }

        .nav-link:hover {
            background-color: #3e556f;
        }

        .logout {
            margin-top: auto;
            background-color: #e74c3c;
            padding: 12px;
            text-align: center;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .logout:hover {
            background-color: #c0392b;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 40px;
            background-color: #f9fafc;
            overflow-y: auto;
        }

        .main-content h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .info-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
            border-left: 5px solid #3498db;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            .nav-link {
                flex: 1 1 45%;
                justify-content: center;
                margin: 5px;
            }

            .logout {
                width: 100%;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Dashboard</h2>

        <div class="user-info">
            <p><strong><?= $username ?></strong></p>
            <p><?= $email ?></p>
            <p><?= $empId ?></p>
        </div>

        <!-- Direct Links -->
        <a class="nav-link" href="apply_leave.php"><i class="fas fa-calendar-check"></i> Apply Leave</a>
        <a class="nav-link" href="cab.php"><i class="fas fa-taxi"></i> Cab Service</a>
        <a class="nav-link" href="settings.php"><i class="fas fa-cogs"></i> Account Settings</a>
        <a class="nav-link" href="payslip.php"><i class="fas fa-file-invoice-dollar"></i> Payslip</a>
        <a class="nav-link" href="emp_appraisal.php"><i class="fas fa-star"></i> Pay Appraisal</a>
        <a class="nav-link" href="attendance.php"><i class="fas fa-clock"></i> Attendance</a>

        <!-- Logout -->
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Welcome, <?= $username ?>!</h1>
        <h1>Let’s go, <?= htmlspecialchars($username) ?>! 🔥</h1>
        <p>Another day, another opportunity to be awesome!</p>

    </div>
</div>

</body>
</html>
