<?php
session_start();
require_once "config.php"; // Your DB connection

// Check if user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Function to fetch users
function fetchUsers($conn) {
    $sql = "SELECT id, username, role, email FROM users";
    return $conn->query($sql);
}

$message = "";

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = htmlspecialchars(trim($_POST['email']));

    if (!empty($username) && !empty($password) && !empty($email)) {
        // Check for duplicate username
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "<p class='error'>Username already exists.</p>";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $passwordHash, $role, $email);
            $stmt->execute();
            $stmt->close();
            $_SESSION['message'] = "User added successfully.";
            header("Location: manage_users1.php");
            exit();
        }
        $checkStmt->close();
    } else {
        $message = "<p class='error'>All fields are required.</p>";
    }
}

// Handle Delete User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $id = intval($_POST['delete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully.";
    } else {
        $_SESSION['message'] = "Failed to delete the user.";
    }
    $stmt->close();
    header("Location: manage_users1.php");
    exit();
}

// Fetch all users
$users = fetchUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
        }

        td {
            background-color: #ffffff;
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        .actions a, .actions button {
            padding: 6px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .actions button {
            background-color: #dc3545;
        }

        .actions a:hover, .actions button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        form {
            display: grid;
            gap: 10px;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
        }

        input[type="text"], input[type="password"], select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
        }

        button[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        .back-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Manage Users</h1>
    <div class="container">
        <a href="admin.php" class="back-link">← Back to Dashboard</a>
        <hr>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<p>{$_SESSION['message']}</p>";
            unset($_SESSION['message']);
        }

        echo $message;
        ?>

        <div class="table-container">
            <h2>All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['id']; ?></td>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                        <td><?= htmlspecialchars($user['role']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td class="actions">
                            <a href="edit_users.php?id=<?= $user['id']; ?>">Edit</a>
                            <form method="POST" action="manage_users1.php" style="display:inline;">
                                <input type="hidden" name="delete_user" value="<?= $user['id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h2>Add New User</h2>
        <form method="POST" action="manage_users1.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>

            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="Employee">Employee</option>
                <option value="Manager">Manager</option>
                <option value="Admin">Admin</option>
            </select>

            <button type="submit" name="add_user">Add User</button>
        </form>
    </div>
</body>
</html>