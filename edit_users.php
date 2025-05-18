<?php
session_start();
require_once "config.php";

// Allow only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $email = htmlspecialchars(trim($_POST['email']));
    $role = $_POST['role'];

    if (!empty($username) && !empty($email)) {
        if (!empty($password)) {
            // Update including password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $passwordHash, $email, $role, $id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $role, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ User updated successfully.";
        } else {
            $_SESSION['message'] = "❌ Failed to update user.";
        }
        $stmt->close();
        header("Location: manage_users1.php");
        exit();
    }
}

// Fetch user data for editing
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $email, $role);
    $stmt->fetch();
    $stmt->close();
} else {
    header("Location: manage_users1.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #1d3557;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: #1d3557;
            margin-bottom: 6px;
            display: block;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            width: 100%;
            background-color: #457b9d;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #1d3557;
        }

        @media (max-width: 500px) {
            form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="edit_users.php">
        <h1>Edit User</h1>
        <input type="hidden" name="id" value="<?= $id; ?>">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>

        <label for="password">New Password (leave blank to keep current):</label>
        <input type="password" id="password" name="password">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>

        <label for="role">Role:</label>
        <select name="role" id="role" required>
            <option value="Employee" <?= $role == 'Employee' ? 'selected' : ''; ?>>Employee</option>
            <option value="Manager" <?= $role == 'Manager' ? 'selected' : ''; ?>>Manager</option>
            <option value="Admin" <?= $role == 'Admin' ? 'selected' : ''; ?>>Admin</option>
        </select>

        <button type="submit" name="edit_user">Update User</button>
    </form>
</body>
</html>
