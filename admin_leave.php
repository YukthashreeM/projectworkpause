<?php
session_start();
require_once "config.php";

// Check if the user is logged in and has the proper role
// if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['Employee', 'Manager'])) {
//     header("Location: login.php");
//     exit();
// }

$user_id = $_SESSION['id'];
$message = "";

// Success message
if (isset($_SESSION['success'])) {
    $message = "<div class='success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}

// Default leave limits
$leave_limits = [
    "Sick Leave" => 5,
    "Casual Leave" => 10,
    "Earned Leave" => 270,
    "Maternity Leave" => 180
];

// Initialize leave counts
$leave_counts = [
    "Sick Leave" => 0,
    "Casual Leave" => 0,
    "Earned Leave" => 0,
    "Maternity Leave" => 0
];

// Count leaves already taken (Pending or Approved)
$sql = "SELECT leave_type, from_date, to_date FROM leaves WHERE user_id = ? AND status IN ('Pending', 'Approved')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_leave = $stmt->get_result();
while ($row = $result_leave->fetch_assoc()) {
    $start = new DateTime($row['from_date']);
    $end = new DateTime($row['to_date']);
    $interval = $start->diff($end);
    $days = $interval->days + 1; // Include both start and end date

    if (isset($leave_counts[$row['leave_type']])) {
        $leave_counts[$row['leave_type']] += $days;
    }
}
$stmt->close();

// Calculate remaining leaves
$remaining_leaves = [];
foreach ($leave_limits as $type => $limit) {
    $used = $leave_counts[$type] ?? 0;
    $remaining = $limit - $used;
    $remaining_leaves[$type] = ($remaining < 0) ? 0 : $remaining;
}

// Form submission logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $leave_type = htmlspecialchars($_POST['leave_type']);
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];
    $reason = htmlspecialchars($_POST['reason']);

    if (!empty($leave_type) && !empty($from) && !empty($to) && !empty($reason)) {
        if (strtotime($from) > strtotime($to)) {
            $message = "<div class='error'>From Date cannot be after To Date.</div>";
        } else {
            // Calculate number of days
            $start = new DateTime($from);
            $end = new DateTime($to);
            $interval = $start->diff($end);
            $leave_days = $interval->days + 1;

            // Validate remaining leaves
            $valid = true;
            $error_text = "";

            if (!isset($remaining_leaves[$leave_type])) {
                $valid = false;
                $error_text = "Invalid leave type selected.";
            } elseif ($leave_days > $remaining_leaves[$leave_type]) {
                $valid = false;
                $error_text = "You only have {$remaining_leaves[$leave_type]} {$leave_type} days remaining.";
            }

            if ($valid) {
                $stmt = $conn->prepare("INSERT INTO leaves (user_id, leave_type, from_date, to_date, reason) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $leave_type, $from, $to, $reason);
                $stmt->execute();
                $stmt->close();
                $_SESSION['success'] = "Leave request submitted successfully.";
                header("Location: apply_leave.php");
                exit();
            } else {
                $message = "<div class='error'>" . $error_text . "</div>";
            }
        }
    } else {
        $message = "<div class='error'>All fields are required.</div>";
    }
}

// Fetch user's leave history
$stmt = $conn->prepare("SELECT leave_type, from_date, to_date, reason, status, created_at FROM leaves WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Leave</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    :root {
        --primary: #3b82f6;         /* Vivid Blue */
        --secondary: #9333ea;       /* Purple */
        --accent: #22d3ee;          /* Light Cyan */
        --text-dark: #1f2937;       /* Dark Gray */
        --bg-main: #f7fafc;         /* Light Gray */
        --card-bg: #ffffff;        /* White */
        --radius: 12px;
        --shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #ffd1dc, #a7c7e7, #f4f7fb);
        background-color: var(--bg-main);
        color: var(--text-dark);
        margin: 0;
        padding: 60px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
    }

    .container, .form-container, .table-container, .remaining-container {
        width: 100%;
        max-width: 900px;
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 30px;
        margin: 20px 0;
        border: 1px solid #e5e7eb;
    }

    h1, h2 {
        text-align: center;
        color: var(--primary);
        margin-bottom: 20px;
        font-weight: 700;
    }

    a {
        text-decoration: none;
        color: var(--secondary);
        font-weight: 600;
        display: block;
        text-align: center;
        margin-bottom: 20px;
        transition: color 0.3s;
    }
    a:hover {
        color: var(--accent);
    }

    input, select, textarea {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: var(--radius);
        background: rgba(255, 255, 255, 0.75);
        font-size: 15px;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.9);
    }

    button {
        width: 100%;
        padding: 14px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        background: linear-gradient(45deg, var(--primary), var(--secondary));
        border: none;
        border-radius: 50px;
        cursor: pointer;
        transition: 0.3s ease;
    }

    button:hover {
        opacity: 0.95;
        transform: scale(1.05);
    }

    .success, .error {
        padding: 14px;
        margin-top: 15px;
        font-weight: 600;
        text-align: center;
        border-radius: var(--radius);
    }

    .success {
        background: #d1fae5;
        color: #065f46;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
    }

    ul {
        list-style-type: none;
        padding: 0;
    }

    ul li {
        background: rgba(255, 255, 255, 0.6);
        margin-bottom: 12px;
        padding: 12px 15px;
        border-radius: var(--radius);
        font-weight: 500;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: rgba(255, 255, 255, 0.6);
        border-radius: var(--radius);
        overflow: hidden;
    }

    th, td {
        padding: 14px;
        text-align: center;
        font-size: 15px;
        background: rgba(255, 255, 255, 0.8);
    }

    th {
        background: var(--primary);
        color: white;
    }

    tr:hover td {
        background: rgba(14, 165, 233, 0.15);
    }

    .status-pending {
        color: #f59e0b;
        font-weight: bold;
    }

    .status-approved {
        color: #10b981;
        font-weight: bold;
    }

    .status-rejected {
        color: #ef4444;
        font-weight: bold;
    }

    #remaining_info {
        font-weight: 600;
        color: var(--primary);
        font-size: 16px;
    }
    </style>
</head>
<body>
    <h1>Leave Application</h1>
    <a href="admin.php">← Back to Dashboard</a>

    <div class="remaining-container">
        <h2>Remaining Leaves</h2>
        <ul>
            <li><strong>Sick Leave:</strong> <?= $remaining_leaves['Sick Leave'] ?> days left</li>
            <li><strong>Casual Leave:</strong> <?= $remaining_leaves['Casual Leave'] ?> days left</li>
            <li><strong>Earned Leave:</strong> <?= $remaining_leaves['Earned Leave'] ?> days left</li>
            <li><strong>Maternity Leave:</strong> <?= $remaining_leaves['Maternity Leave'] ?> days left</li>
        </ul>
    </div>

    <div class="form-container">
        <?= $message ?>
        <form method="POST">
            <label>Type of Leave:</label>
            <select name="leave_type" id="leave_type" onchange="updateRemainingLeave()" required>
                <option value="">--Select Leave Type--</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Casual Leave">Casual Leave</option>
                <option value="Earned Leave">Earned Leave</option>
                <option value="Maternity Leave">Maternity Leave</option>
            </select>
            <p id="remaining_info" style="color: #555; font-weight: bold; margin-bottom: 10px;"></p>

            <label>From Date:</label>
            <input type="date" name="from_date" required>

            <label>To Date:</label>
            <input type="date" name="to_date" required>

            <label>Reason:</label>
            <textarea name="reason" rows="4" required></textarea>

            <button type="submit" onclick="return confirm('Are you sure you want to submit this leave request?')">Submit Leave</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Your Leave Requests</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Applied On</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td><?= htmlspecialchars($row['from_date']) ?></td>
                    <td><?= htmlspecialchars($row['to_date']) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td>
                        <?php if ($row['status'] === 'Pending'): ?>
                            <span style="color: orange;">Pending</span>
                        <?php elseif ($row['status'] === 'Approved'): ?>
                            <span style="color: green;">Approved</span>
                        <?php else: ?>
                            <span style="color: red;">Rejected</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        const remainingLeaves = {
            "Sick Leave": <?= $remaining_leaves['Sick Leave'] ?>,
            "Casual Leave": <?= $remaining_leaves['Casual Leave'] ?>,
            "Earned Leave": <?= $remaining_leaves['Earned Leave'] ?>,
            "Maternity Leave": <?= $remaining_leaves['Maternity Leave'] ?>
        };

        function updateRemainingLeave() {
            const selectedType = document.getElementById('leave_type').value;
            const info = document.getElementById('remaining_info');
            if (remainingLeaves[selectedType] !== undefined) {
                info.textContent = `You have ${remainingLeaves[selectedType]} days left for ${selectedType}.`;
            } else {
                info.textContent = "";
            }
        }
    </script>
</body>
</html>