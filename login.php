<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE BINARY username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "User not found!";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Incorrect password!";
        } else {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: " . strtolower($user['role']) . ".php");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Company Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@300;600&family=Playfair+Display:ital,wght@1,700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #ffd1dc, #a7c7e7, #f4f7fb);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: #333;
        }

        .login-box {
            position: relative;
            z-index: 1;
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-box h2 {
            font-size: 34px;
            font-family: 'Playfair Display', serif;
            font-style: italic;
            color: #5c6bc0;
            margin-bottom: 25px;
            letter-spacing: 2px;
        }

        .login-box img {
            width: 80px;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            margin: 12px 0;
            border: 2px solid #ddd;
            border-radius: 10px;
            background-color: #f0f4f8;
            color: #333;
            font-size: 18px;
            font-family: 'Roboto', sans-serif;
            transition: 0.3s ease;
        }

        input::placeholder {
            color: #888;
            font-style: italic;
        }

        input:focus {
            outline: none;
            border-color: #5c6bc0;
            background-color: #ffffff;
            box-shadow: 0 0 0 2px #5c6bc0;
        }

        button {
            width: 100%;
            padding: 15px;
            margin-top: 18px;
            background: black; /* Blue color */
            border: none;
            border-radius: 10px;
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: grey;
            transform: scale(1.05);
        }

        .error {
            margin-top: 15px;
            color: #ff6b6b;
            font-size: 16px;
            font-weight: 500;
            animation: shake 0.4s;
        }

        .support-links {
            margin-top: 20px;
            font-size: 14px;
            color: #5c6bc0;
        }

        .support-links a {
            text-decoration: none;
            color: #5c6bc0;
            margin: 0 10px;
        }

        .footer {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 14px;
            color: black;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            50% { transform: translateX(4px); }
            75% { transform: translateX(-4px); }
            100% { transform: translateX(0); }
        }

    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
        <div class="support-links">
        <a href="request_reset.php">Forgot Password</a>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
     </div>
    

    <div class="footer">
        <p>&copy; 2025 <b><i>WorkPause</i></b>.All Rights Reserved.</p>
    </div>
</body>
</html>