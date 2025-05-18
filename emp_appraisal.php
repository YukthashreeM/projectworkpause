<?php
session_start();
require 'db.php';

// Redirect if not logged in or not an employee
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Employee') {
    header("Location: login.php");
    exit();
}

$employeeId = $_SESSION['id'];
$username = $_SESSION['username'];

try {
    $stmt = $pdo->prepare("
        SELECT u.username, a.appraisal_score, a.salary_increment 
        FROM users u
        JOIN appraisal a ON u.id = a.employee_id
        WHERE u.id = ?
    ");
    $stmt->execute([$employeeId]);
    $appraisal = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appraisal</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #ece9e6, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h2 {
            color: #5c6bc0;
            font-family: 'Playfair Display', serif;
            margin-bottom: 30px;
        }

        .info {
            margin: 15px 0;
            font-size: 18px;
            color: #333;
        }

        .info span {
            font-weight: bold;
            color: #000;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Appraisal Report</h2>

        <div class="info">
            Username: <span><?= htmlspecialchars($username) ?></span>
        </div>

        <?php if ($appraisal): ?>
            <div class="info">
                Appraisal Score: <span><?= htmlspecialchars($appraisal['appraisal_score']) ?></span>
            </div>
            <div class="info">
                Salary Increment: <span>$<?= htmlspecialchars($appraisal['salary_increment']) ?></span>
            </div>
        <?php else: ?>
            <div class="info">No appraisal record found.</div>
        <?php endif; ?>

        <div class="footer">
            &copy; 2025 <b><i>WorkPause</i></b>. All Rights Reserved.
        </div>
    </div>
</body>
</html>
