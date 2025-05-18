<?php
session_start();
require_once "config.php"; // Ensure config.php contains the DB connection

// Set the default timezone (replace with your timezone)
date_default_timezone_set('Asia/Kolkata');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$message = "";

// Get today's date
$today = date('Y-m-d');

// Initialize the shift details
$assigned_shift = [];
$role = "";

// Fetch the user role from the database
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

// Fetch the shift details from the database
$stmt = $conn->prepare("SELECT s.name, s.check_in_time, s.check_out_time, s.id AS shift_id 
                       FROM user_shift_assignment us 
                       JOIN shifts s ON us.shift_id = s.id 
                       WHERE us.user_id = ? AND us.assigned_date = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$stmt->bind_result($shift_name, $shift_start, $shift_end, $shift_id);
if ($stmt->fetch()) {
    $assigned_shift = [
        'shift_name' => $shift_name,
        'shift_start' => $shift_start,
        'shift_end' => $shift_end,
        'shift_id' => $shift_id
    ];
}
$stmt->close();

if (empty($assigned_shift)) {
    $message = "You don't have a shift assigned for today.";
    $shift_name = "No shift assigned";  // Fallback value for shift name
    $shift_start = $shift_end = ""; // Fallback shift times
} else {
    // Get current time
    $now = new DateTime();
    $now_str = $now->format('Y-m-d H:i:s');  // Correct format for MySQL

    // Fetch if the user has already checked in today
    $attendance = [];
    $stmt = $conn->prepare("SELECT * FROM attendance_logs WHERE user_id = ? AND date = ? AND check_out IS NULL");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $attendance = $row;  // This means the user is checked in
    }
    $stmt->close();

    // Handle check-in or check-out
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $action = $_POST['action'];  // 'check_in' or 'check_out'

        if ($action === 'check_in') {
            // Insert check-in time into the database
            $stmt = $conn->prepare("INSERT INTO attendance_logs (user_id, shift_id, date, check_in) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $shift_id, $today, $now_str);
            if ($stmt->execute()) {
                $message = "Checked in at $now_str (Shift: $shift_name).";
            } else {
                $message = "Failed to mark check-in. Error: " . $stmt->error;  // Added error message
            }
            $stmt->close();
        } elseif ($action === 'check_out') {
            // Update check-out time in the database
            $stmt = $conn->prepare("UPDATE attendance_logs SET check_out = ? WHERE user_id = ? AND date = ? AND check_out IS NULL");
            $stmt->bind_param("sis", $now_str, $user_id, $today);
            if ($stmt->execute()) {
                $message = "Checked out at $now_str.";
            } else {
                $message = "Failed to mark check-out. Error: " . $stmt->error;  // Added error message
            }
            $stmt->close();
        }
    }
}

// Set the dashboard link based on user role
$dashboard_link = '';
switch ($role) {
    case 'Admin':
        $dashboard_link = 'admin.php';
        break;
    case 'Manager':
        $dashboard_link = 'manager.php';
        break;
    case 'Employee':
        $dashboard_link = 'employee.php';
        break;
    default:
        $dashboard_link = 'login.php'; // Redirect to login if role is not found
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance</title>
    <style>
        /* General body and container styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            font-size: 32px;
            color: #495057;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .shift-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 30px;
            font-size: 18px;
            color: #007bff;
        }
        .message {
            font-size: 20px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .attendance-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 20px;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        .attendance-btn:hover {
            background-color: #0056b3;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .attendance-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .attendance-log {
            margin-top: 40px;
            font-size: 18px;
            color: #343a40;
        }
        .log-item {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .log-item span {
            color: #007bff;
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Attendance for <?= htmlspecialchars($shift_name) ?> Shift</h2>

    <div class="shift-info">
        <p><strong>Shift Timings:</strong> <?= htmlspecialchars($shift_start) ?> to <?= htmlspecialchars($shift_end) ?></p>
    </div>

    <p class="message"><?= htmlspecialchars($message) ?></p>

    <?php if (!empty($assigned_shift)): ?>
        <!-- Check if the user has already checked in or not -->
        <?php if (empty($attendance)): ?>
            <form method="POST">
                <button type="submit" name="action" value="check_in" class="attendance-btn">Check In</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="action" value="check_out" class="attendance-btn">Check Out</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Display the attendance logs -->
    <div class="attendance-log">
        <h3>Attendance Logs</h3>
        <?php
        // Fetch and display the user's attendance logs for today
        $logs_stmt = $conn->prepare("SELECT check_in, check_out FROM attendance_logs WHERE user_id = ? AND date = ? ORDER BY check_in ASC");
        $logs_stmt->bind_param("is", $user_id, $today);
        $logs_stmt->execute();
        $logs_result = $logs_stmt->get_result();
        $logs = [];
        while ($log = $logs_result->fetch_assoc()) {
            $logs[] = $log;
        }
        $logs_stmt->close();

        if (!empty($logs)) {
            $first_check_in = new DateTime($logs[0]['check_in']);
            $last_check_out = new DateTime($logs[count($logs) - 1]['check_out']);
            $total_duration = $first_check_in->diff($last_check_out);

            echo "<div class='log-header'>Check-in / Check-out Times</div>";
            echo "<ul>";
            foreach ($logs as $log) {
                $check_in = new DateTime($log['check_in']);
                $check_out = isset($log['check_out']) ? new DateTime($log['check_out']) : null;

                $duration = '';
                if ($check_out) {
                    $duration = $check_in->diff($check_out)->format('%h hours, %i minutes');
                }

                echo "<li class='log-item'>Checked In: <span>" . $check_in->format('Y-m-d H:i:s') . "</span>";
                if ($check_out) {
                    echo " - Checked Out: <span>" . $check_out->format('Y-m-d H:i:s') . "</span>";
                    echo " - Duration: <span>$duration</span>";
                } else {
                    echo " - Not yet Checked Out";
                }
                echo "</li>";
            }
            echo "</ul>";

            // Display total duration between first check-in and last check-out
            echo "<p><strong>Total Duration:</strong> " . $total_duration->format('%h hours, %i minutes') . "</p>";
        } else {
            echo "<p>No check-ins for today yet.</p>";
        }
        ?>
    </div>

    <!-- Dynamic "Back to Dashboard" Button based on Role -->
    <a href="<?= $dashboard_link ?>" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>
