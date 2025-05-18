<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php'; // This defines $pdo

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'Employee'; // default role fallback
$message = "";

// Fetch current user details
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle Profile Update (Password and Email)
if (isset($_POST['update_profile'])) {
    $new_email = $_POST['email'];
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // If a password is set, validate and update it
    if ($new_password && $new_password !== $confirm_password) {
        $message = "❌ New passwords do not match.";
    } elseif ($new_password) {
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE username = ?");
        $stmt->execute([$new_email, $new_password_hash, $username]);
        $message = "✅ Profile updated successfully!";
    } else {
        // Only update email if no password change
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE username = ?");
        $stmt->execute([$new_email, $username]);
        $message = "✅ Profile updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #eef2f7;
            padding: 40px;
        }
        .card {
            background-color: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="email"], input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #2f80ed;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #1c5db8;
        }
        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #2f80ed;
        }
        @media (max-width: 600px) {
            .card {
                width: 90%;
            }
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Update Profile</h2>
    <form method="post">
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" readonly placeholder="Username">
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" required>
        <input type="password" name="new_password" placeholder="New Password (Optional)">
        <input type="password" name="confirm_password" placeholder="Confirm New Password">
        <input type="submit" name="update_profile" value="Update Profile">
    </form>

    <div class="message <?= (strpos($message, '✅') !== false) ? 'success' : (strpos($message, '❌') !== false ? 'error' : '') ?>">
        <?= htmlspecialchars($message) ?>
    </div>

    <a href="request_reset.php">Forgot Password?</a>
    <a href="<?= ($role === 'Admin') ? 'admin.php' : (($role === 'Manager') ? 'manager.php' : 'employee.php') ?>">← Back to Dashboard</a>
</div>
</body>
</html>