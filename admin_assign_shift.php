<?php
// admin_assign_shift.php
session_start();
require_once "config.php";

// Admin validation
if ($_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch users and shifts for assignment
$users = [];
$shifts = [];

$user_query = "SELECT * FROM users";
$shift_query = "SELECT * FROM shifts";

$user_result = $conn->query($user_query);
$shift_result = $conn->query($shift_query);

while ($user_row = $user_result->fetch_assoc()) {
    $users[] = $user_row;
}

while ($shift_row = $shift_result->fetch_assoc()) {
    $shifts[] = $shift_row;
}

// Handle form submission to assign shifts
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $shift_id = $_POST['shift_id'];
    $assigned_date = $_POST['assigned_date'];

    $stmt = $conn->prepare("INSERT INTO user_shift_assignment (user_id, shift_id, assigned_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $shift_id, $assigned_date);
    
    if ($stmt->execute()) {
        $message = "Shift assigned successfully!";
    } else {
        $message = "Failed to assign shift!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Assign Shift</title>
</head>
<body>
    <h2>Assign Shift to User</h2>
    <form method="POST">
        <label for="user_id">Select User:</label>
        <select name="user_id" required>
            <?php foreach ($users as $user) { ?>
                <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
            <?php } ?>
        </select><br>

        <label for="shift_id">Select Shift:</label>
        <select name="shift_id" required>
            <?php foreach ($shifts as $shift) { ?>
                <option value="<?= $shift['id'] ?>"><?= $shift['shift_name'] ?></option>
            <?php } ?>
        </select><br>

        <label for="assigned_date">Assigned Date:</label>
        <input type="date" name="assigned_date" required><br>

        <button type="submit">Assign Shift</button>
    </form>

    <?= isset($message) ? "<p>$message</p>" : '' ?>

    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>