<?php
// admin_assign.php
require_once "config.php";
session_start();

// Check if admin (uncomment if needed)
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: login.php");
//     exit();
// }

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $shift_id = $_POST['shift_id'];
    $assigned_date = $_POST['assigned_date'];

    // Check if assignment already exists
    $check = $conn->prepare("SELECT * FROM user_shift_assignment WHERE user_id = ? AND assigned_date = ?");
    $check->bind_param("is", $user_id, $assigned_date);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "❌ User already has a shift assigned for this date.";
    } else {
        $stmt = $conn->prepare("INSERT INTO user_shift_assignment (user_id, shift_id, assigned_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $shift_id, $assigned_date);
        $message = $stmt->execute() ? "✅ Shift assigned successfully!" : "❌ Failed to assign shift.";
        $stmt->close();
    }
    $check->close();
}

// Fetch users and shifts
$userResult = $conn->query("SELECT id, username FROM users");
$shiftResult = $conn->query("SELECT id, name, check_in_time, check_out_time FROM shifts");

// Fetch assigned shifts
$assignedShiftsResult = $conn->query("
    SELECT u.username, s.name AS shift_name, usa.assigned_date 
    FROM user_shift_assignment usa
    JOIN users u ON usa.user_id = u.id
    JOIN shifts s ON usa.shift_id = s.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Shift</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 700px;
            margin-top: 40px;
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }
        select, input[type="date"], button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
        }
        .success { color: green; }
        .error { color: red; }
        a {
            text-decoration: none;
            color: #007bff;
            display: inline-block;
            margin-top: 15px;
        }
        .assigned-shifts {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Assign Shift to User</h2>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="user_id">User:</label>
            <select name="user_id" required>
                <option value="">Select User</option>
                <?php while ($user = $userResult->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="shift_id">Shift:</label>
            <select name="shift_id" required>
                <option value="">Select Shift</option>
                <?php while ($shift = $shiftResult->fetch_assoc()): ?>
                    <option value="<?= $shift['id'] ?>">
                        <?= htmlspecialchars($shift['name']) ?> 
                        (<?= substr($shift['check_in_time'], 0, 5) ?> - <?= substr($shift['check_out_time'], 0, 5) ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="assigned_date">Date:</label>
            <input type="date" name="assigned_date" required>

            <button type="submit">Assign Shift</button>
        </form>

        <a href="admin.php">← Back to Dashboard</a>

        <div class="assigned-shifts">
            <h3>Assigned Shifts</h3>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Shift</th>
                        <th>Assigned Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $assignedShiftsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['shift_name']) ?></td>
                            <td><?= htmlspecialchars($row['assigned_date']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
