<?php
session_start();
$resetMessage = "";

// Check if the OTP is still valid (if not expired and matching)
if (!isset($_SESSION['otp']) || $_SESSION['otp_expiry'] < time()) {
    $resetMessage = "❌ OTP expired or invalid. Please request a new OTP.";
} else {
    // Proceed with password reset if OTP is valid
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'], $_POST['password'])) {
        $otpEntered = $_POST['otp'];
        $newPassword = $_POST['password'];

        // Check if OTP matches session OTP and is not expired
        if ($_SESSION['otp'] == $otpEntered && $_SESSION['otp_expiry'] > time()) {
            // Hash new password and update it in the database
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Connect to the database (Make sure to replace this with your actual database connection)
            require 'db.php'; // Assumes you have a connection file for DB

            // Update password in the database
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':email', $_SESSION['email']);
            $stmt->execute();

            // Clear OTP session data after password reset
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiry']);
            unset($_SESSION['email']);

            $resetMessage = "Your password has been successfully reset. You can now <a href='login.php'>login</a>.";
        } else {
            $resetMessage = "❌ Invalid OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Your styling goes here, it could be reused from previous pages */
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
        input[type="text"], input[type="password"], button {
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
        .message-box {
            background-color: #f1f1f1;
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            color: #000;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>
    
    <!-- Form to reset password -->
    <form method="POST" action="">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" required placeholder="Enter OTP" value="<?= isset($_POST['otp']) ? $_POST['otp'] : '' ?>" />

        <label for="password">Enter New Password:</label>
        <input type="password" name="password" required placeholder="Enter new password" />

        <button type="submit">Reset Password</button>
    </form>

    <!-- Show messages (success or error) -->
    <?php
    if (!empty($resetMessage)) {
        echo "<div class='message-box'>$resetMessage</div>";
    }
    ?>

</div>

</body>
</html>
