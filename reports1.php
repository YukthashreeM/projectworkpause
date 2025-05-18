<?php
session_start();
require_once "config.php"; // Your database connection file

// Redirect if not logged in or not an Admin
//if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
  //  header("Location: login.php");
   // exit();
//}

// Fetch total leaves
$totalLeavesQuery = "SELECT COUNT(*) AS total FROM leaves";
$totalLeavesResult = $conn->query($totalLeavesQuery);
$totalLeaves = $totalLeavesResult->fetch_assoc()['total'];

// Fetch approved leaves
$approvedLeavesQuery = "SELECT COUNT(*) AS approved FROM leaves WHERE status = 'Approved'";
$approvedLeavesResult = $conn->query($approvedLeavesQuery);
$approvedLeaves = $approvedLeavesResult->fetch_assoc()['approved'];

// Fetch rejected leaves
$rejectedLeavesQuery = "SELECT COUNT(*) AS rejected FROM leaves WHERE status = 'Rejected'";
$rejectedLeavesResult = $conn->query($rejectedLeavesQuery);
$rejectedLeaves = $rejectedLeavesResult->fetch_assoc()['rejected'];

// Fetch pending leaves
$pendingLeavesQuery = "SELECT COUNT(*) AS pending FROM leaves WHERE status = 'Pending'";
$pendingLeavesResult = $conn->query($pendingLeavesQuery);
$pendingLeaves = $pendingLeavesResult->fetch_assoc()['pending'];

// Fetch leave requests for detailed report
$leaveDetailsQuery = "SELECT l.*, u.username FROM leaves l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC";
$leaves = $conn->query($leaveDetailsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Reports</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f0f4f8, #dfe9f3);
            background: linear-gradient(135deg, #ffd1dc, #a7c7e7, #f4f7fb);
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 2.8rem;
            color: #2c3e50;
            margin-bottom: 40px;
        }

        .stats-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 50px;
        }

        .stats-box {
            background: #ffffff;
            padding: 25px 35px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            width: 230px;
            transition: 0.3s ease;
        }

        .stats-box:hover {
            transform: translateY(-5px);
        }

        .stats-box h2 {
            font-size: 1rem;
            color: #777;
            margin-bottom: 10px;
        }

        .stats-box p {
            font-size: 2.2rem;
            font-weight: bold;
            color: #007bff;
        }

        .logout-btn, .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            margin: 0 10px 30px 0;
            transition: background 0.3s;
        }

        .logout-btn {
            background-color: #e74c3c;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            background-color: transparent;
        }

        th, td {
            padding: 15px;
            background: #fff;
            text-align: left;
            vertical-align: middle;
            font-size: 0.95rem;
        }

        th {
            background: #007bff;
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        tr {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-radius: 6px;
        }

        tr:hover td {
            background-color: #f8f9fa;
        }

        .action-links a {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #fff;
            text-decoration: none;
            margin-right: 5px;
        }

        .approve {
            background-color: #28a745;
        }

        .reject {
            background-color: #dc3545;
        }

        .action-links a:hover {
            opacity: 0.9;
        }

        .status.approved {
            color: #28a745;
            font-weight: 600;
        }

        .status.rejected {
            color: #dc3545;
            font-weight: 600;
        }

        .status.pending {
            color: #ffc107;
            font-weight: 600;
        }

        .nav-container {
            text-align: center;
        }

    </style>
</head>
<body>

<h1>Leave Reports</h1>

<div class="nav-container">
    <a href="manager.php" class="btn">← Back to Dashboard</a>
</div>

<div class="stats-container">
    <div class="stats-box">
        <h2>Total Leaves</h2>
        <p><?= $totalLeaves ?></p>
    </div>
    <div class="stats-box">
        <h2>Approved</h2>
        <p><?= $approvedLeaves ?></p>
    </div>
    <div class="stats-box">
        <h2>Rejected</h2>
        <p><?= $rejectedLeaves ?></p>
    </div>
    <div class="stats-box">
        <h2>Pending</h2>
        <p><?= $pendingLeaves ?></p>
    </div>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>From</th>
        <th>To</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $leaves->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= $row['from_date'] ?></td>
            <td><?= $row['to_date'] ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><span class="status <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
            <td class="action-links">
                <?php if ($row['status'] === 'Pending'): ?>
                    <a href="?action=approve&id=<?= $row['id'] ?>" class="approve">Approve</a>
                    <a href="?action=reject&id=<?= $row['id'] ?>" class="reject">Reject</a>
                <?php else: ?>
                    <em style="color: #999;">No action</em>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>