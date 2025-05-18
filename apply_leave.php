<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['Employee', 'Manager', 'Admin'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$user_role = $_SESSION['role'];
$message = "";

// Success message
if (isset($_SESSION['success'])) {
    $message = "<div class='success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}

// Get user joining date
$stmt = $conn->prepare("SELECT joining_date FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_join = $stmt->get_result();
$user = $result_join->fetch_assoc();
$joining_date = $user['joining_date'] ?? date('Y-01-01'); // fallback Jan 1 if missing
$stmt->close();

// Calculate leave factor based on joining date
$current_year = date('Y');
$year_start = new DateTime("$current_year-01-01");
$year_end = new DateTime("$current_year-12-31");
$joining = new DateTime($joining_date);

if ($joining > $year_end) {
    $leave_factor = 0; // Joined after current year — no leave for this year
} elseif ($joining < $year_start) {
    $leave_factor = 1; // Joined before current year — full leave
} else {
    // Joined during current year — prorate leaves
    $days_remaining = $joining->diff($year_end)->days + 1; // inclusive
    $total_days = $year_start->diff($year_end)->days + 1;
    $leave_factor = $days_remaining / $total_days;
}

// Full leave limits
$full_leave_limits = [
    "Sick Leave" => 5,
    "Casual Leave" => 10,
    "Earned Leave" => 270,
    "Maternity Leave" => 180 // no proration
];

// Apply proration ONLY to Sick, Casual, Earned leaves
$leave_limits = [];
foreach ($full_leave_limits as $type => $limit) {
    if (in_array($type, ['Sick Leave', 'Casual Leave', 'Earned Leave'])) {
        $leave_limits[$type] = floor($limit * $leave_factor);
    } else {
        $leave_limits[$type] = $limit;
    }
}

// Count leaves already taken this year (Pending or Approved)
$leave_counts = array_fill_keys(array_keys($leave_limits), 0);

$sql = "SELECT leave_type, from_date, to_date FROM leaves 
        WHERE user_id = ? AND status IN ('Pending', 'Approved') 
        AND YEAR(from_date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $current_year);
$stmt->execute();
$result_leave = $stmt->get_result();

while ($row = $result_leave->fetch_assoc()) {
    $start = new DateTime($row['from_date']);
    $end = new DateTime($row['to_date']);
    $days = $start->diff($end)->days + 1;
    $leave_counts[$row['leave_type']] += $days;
}
$stmt->close();

// Calculate remaining leaves after leaves taken
$remaining_leaves = [];
foreach ($leave_limits as $type => $limit) {
    $remaining_leaves[$type] = max(0, $limit - $leave_counts[$type]);
}

// Handle leave form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $leave_type = htmlspecialchars($_POST['leave_type']);
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];
    $reason = htmlspecialchars($_POST['reason']);

    if ($leave_type && $from && $to && $reason) {
        if (strtotime($from) > strtotime($to)) {
            $message = "<div class='error'>From Date cannot be after To Date.</div>";
        } else {
            $leave_days = (new DateTime($from))->diff(new DateTime($to))->days + 1;

            if (!isset($remaining_leaves[$leave_type])) {
                $message = "<div class='error'>Invalid leave type.</div>";
            } elseif ($leave_days > $remaining_leaves[$leave_type]) {
                $message = "<div class='error'>Only {$remaining_leaves[$leave_type]} day(s) left for $leave_type.</div>";
            } else {
                $stmt = $conn->prepare("INSERT INTO leaves (user_id, leave_type, from_date, to_date, reason) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $leave_type, $from, $to, $reason);
                $stmt->execute();
                $stmt->close();
                $_SESSION['success'] = "Leave request submitted successfully.";
                header("Location: apply_leave.php");
                exit();
            }
        }
    } else {
        $message = "<div class='error'>All fields are required.</div>";
    }
}

// Fetch leave history
$stmt = $conn->prepare("SELECT leave_type, from_date, to_date, reason, status, created_at FROM leaves WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Dashboard link by role
$dashboard = match ($user_role) {
    'Admin' => 'admin.php',
    'Manager' => 'manager.php',
    'Employee' => 'employee.php',
    default => '#',
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Leave</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        header {
            background: #34495e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: #f1c40f;
            text-decoration: none;
            font-weight: bold;
        }
        main {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .success {
            background: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .error {
            background: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        form input, form select, form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        form button {
            margin-top: 20px;
            background: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        form button:hover {
            background: #2980b9;
        }
        .leave-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .leave-table th, .leave-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        .leave-table th {
            background-color: #ecf0f1;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        ul.remaining {
            list-style: none;
            padding-left: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }
        ul.remaining li {
            background: #e8f6f3;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
            color: #2c3e50;
        }
        #remaining_info {
            color: #7f8c8d;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<header>
    <h1>Apply for Leave</h1>
    <a href="<?= $dashboard ?>">← Back to Dashboard</a>
</header>
<main>
    <div class="card">
        <h2>Remaining Leave Balances (<?= $current_year ?>)</h2>
        <ul class="remaining">
            <?php foreach ($remaining_leaves as $type => $days): ?>
                <li><?= htmlspecialchars($type) ?>: <?= $days ?> day(s)</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <?= $message ?>
        <h2>Submit New Leave Request</h2>
        <form method="POST">
            <label>Type of Leave</label>
            <select name="leave_type" id="leave_type" onchange="updateRemainingLeave()" required>
                <option value="">--Select Leave Type--</option>
                <?php foreach ($leave_limits as $type => $limit): ?>
                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                <?php endforeach; ?>
            </select>
            <p id="remaining_info"></p>

            <label>From Date</label>
            <input type="date" name="from_date" required>

            <label>To Date</label>
            <input type="date" name="to_date" required>

            <label>Reason</label>
            <textarea name="reason" rows="4" required></textarea>

            <button type="submit" onclick="return confirm('Submit this leave request?')">Submit Leave</button>
        </form>
    </div>

    <div class="card leave-table">
        <h2>Leave History</h2>
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
                    <td class="status-<?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</main>

<script>
    const remainingLeaves = <?= json_encode($remaining_leaves) ?>;
    function updateRemainingLeave() {
        const type = document.getElementById("leave_type").value;
        const info = document.getElementById("remaining_info");
        if (type && remainingLeaves[type] !== undefined) {
            info.textContent = `Remaining ${type}: ${remainingLeaves[type]} day(s)`;
        } else {
            info.textContent = "";
        }
    }
</script>

</body>
</html>
