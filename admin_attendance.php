<?php
session_start();
require_once "config.php";

// Only Admins allowed
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Get all user shift assignments with shift times
$sql = "
    SELECT 
        u.username,
        usa.user_id,
        usa.assigned_date AS date,
        s.name AS shift_name,  -- Corrected to use s.name for shift name
        s.check_in_time AS shift_start,  -- Corrected to use check_in_time
        s.check_out_time AS shift_end,  -- Corrected to use check_out_time
        s.id AS shift_id
    FROM user_shift_assignment usa
    JOIN users u ON u.id = usa.user_id
    JOIN shifts s ON s.id = usa.shift_id
    ORDER BY usa.assigned_date DESC, u.username
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Status</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; background-color: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #4CAF50; color: white; }
        .present { color: green; font-weight: bold; }
        .absent { color: red; font-weight: bold; }
        a { margin-top: 20px; display: inline-block; color: #4CAF50; }
    </style>
</head>
<body>

<h2>Attendance Summary with Status</h2>

<table>
    <tr>
        <th>Username</th>
        <th>Date</th>
        <th>Shift</th>
        <th>Status</th>
        <th>Total Duration</th>
    </tr>

<?php
if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $user_id = $row['user_id'];
        $shift_id = $row['shift_id'];
        $date = $row['date'];
        $shift_start = $row['shift_start'];
        $shift_end = $row['shift_end'];

        $shift_start_dt = "$date $shift_start";
        $shift_end_dt = (strtotime($shift_end) < strtotime($shift_start)) 
                        ? date("Y-m-d H:i:s", strtotime("$date $shift_end +1 day"))
                        : "$date $shift_end";

        // Fetch all attendance logs for that user/date/shift
        $log_sql = "
            SELECT check_in, check_out
            FROM attendance_logs
            WHERE user_id = ? AND shift_id = ? AND date = ?
        ";
        $stmt = $conn->prepare($log_sql);
        $stmt->bind_param("iis", $user_id, $shift_id, $date);
        $stmt->execute();
        $logs = $stmt->get_result();

        $has_checkin = false;
        $total_seconds = 0;

        while ($log = $logs->fetch_assoc()) {
            if (!empty($log['check_in'])) {
                $has_checkin = true;

                if (!empty($log['check_out'])) {
                    $in = strtotime($log['check_in']);
                    $out = strtotime($log['check_out']);
                    $total_seconds += max(0, $out - $in);
                }
            }
        }

        $stmt->close();

        $status = $has_checkin ? 'Present' : 'Absent';
        $status_class = $has_checkin ? 'present' : 'absent';
        $duration = $has_checkin ? gmdate("H:i:s", $total_seconds) : '-';
?>

    <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($date) ?></td>
        <td><?= htmlspecialchars($row['shift_name']) ?></td>
        <td class="<?= $status_class ?>"><?= $status ?></td>
        <td><?= $duration ?></td>
    </tr>

<?php
    endwhile;
else:
?>
    <tr><td colspan="5">No records found.</td></tr>
<?php endif; ?>
</table>

<a href="admin.php">← Back to Dashboard</a>

</body>
</html>
