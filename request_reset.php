<?php
session_start();
$otpMessage = "";

// Optional: Role-based dashboard redirection
$dashboardLink = 'dashboard.php';
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Admin': $dashboardLink = 'admin.php'; break;
        case 'Manager': $dashboardLink = 'manager.php'; break;
        case 'Employee': $dashboardLink = 'employee.php'; break;
    }
}

// Composer autoload for PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // TODO: Check if email exists in your user database
    // $userExists = true; // Set to false if not found
    // if (!$userExists) {
    //     $otpMessage = "<div class='otp-box'><p>If the email exists, an OTP will be sent.</p></div>";
    //     exit();
    // }

    // Generate a 4-digit OTP
    $otp = rand(1000, 9999);

    // Store OTP, email, and expiry in session
    $_SESSION['otp'] = $otp;
    $_SESSION['email'] = $email;
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes validity

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // <-- Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yukthashreem@gmail.com';   // <-- SMTP username
        $mail->Password   = 'dvaa zmhl aovy ggue';     // <-- SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('yukthashreem@gmail.com', 'workpause'); // <-- Update this
        $mail->addAddress($email);

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'Your OTP Code for Password Reset';
        $mail->Body    = "Dear user,\n\nYour OTP code is: $otp\n\nIt is valid for 5 minutes.\n\nIf you did not request this, please ignore this email.";

        $mail->send();
        $otpMessage = "<div class='otp-box'><p>OTP has been sent to <strong>$email</strong>.</p><a href='reset_password.php'>Click here to verify OTP</a></div>";#changed here from verify_otp.php
    } catch (Exception $e) {
        $otpMessage = "<div class='otp-box'><p style='color:red;'>Failed to send OTP email. Mailer Error: {$mail->ErrorInfo}</p></div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Step 1</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6B73FF, rgb(151, 221, 249));
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h2 {
            margin-bottom: 20px;
            color: #000DFF;
        }
        input[type="email"], button {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background-color: #000DFF;
            color: white;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: rgba(70, 170, 217, 0.83);
        }
        .otp-box {
            background-color: #f1f1f1;
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            color: #000;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            color: rgb(104, 107, 181);
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .back-btn {
            background-color:lightblue ;
            margin-top: 10px;
        }
        .back-btn:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Forgot Password</h2>
    <form method="POST" action="">
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit">Generate OTP</button>
    </form>

    <?php
    if (!empty($otpMessage)) {
        echo $otpMessage;
    }
    ?>

    <a href="<?= $dashboardLink ?>"><button class="back-btn">⬅️ Back to Dashboard</button></a>
</div>

</body>
</html>
