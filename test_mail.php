<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yukthashreem@gmail.com';       // ✅ Replace with your Gmail
    $mail->Password = 'Yuktha@6367';          // ✅ Replace with Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Debugging (for testing)
    $mail->SMTPDebug = 2;  // Show full debug output
    $mail->Debugoutput = 'html';

    // Recipients
    $mail->setFrom('your_email@gmail.com', 'Test Mailer');
    $mail->addAddress('recipient@example.com', 'Recipient Name'); // Replace with your real address

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer SMTP Test';
    $mail->Body    = 'This is a test email using Gmail SMTP and PHPMailer.';
    $mail->AltBody = 'This is a plain-text version for non-HTML clients.';

    $mail->send();
    echo '✅ Test email sent successfully!';
} catch (Exception $e) {
    echo "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
