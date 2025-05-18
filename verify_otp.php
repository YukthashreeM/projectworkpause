<?php
session_start();
$otpMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $otpEntered = $_POST['otp'];

    // Check if the OTP exists in session and is not expired
    if (isset($_SESSION['otp']) && $_SESSION['otp'] == $otpEntered) {
        if ($_SESSION['otp_expiry'] > time()) {
            // OTP is correct and not expired
            // Proceed to allow the user to set a new password
            header("Location: reset_password.php");
            exit();
        } else {
            $otpMessage = "❌ OTP expired. Please request a new OTP.";
        }
    } else {
        $otpMessage = "❌ Invalid OTP. Please try again.";
    }
}
?>

<!-- OTP Verification HTML -->
<form method="POST" action="verify_otp.php">
    <input type="text" name="otp" required placeholder="Enter OTP">
    <button type="submit">Verify OTP</button>
</form>

<?php if ($otpMessage): ?>
    <p style="color:red;"><?= $otpMessage ?></p>
<?php endif; ?>
