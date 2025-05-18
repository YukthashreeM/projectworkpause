<?php
session_start();
require_once "config.php";

// Check admin access
//if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
  //  header("Location: login.php");
    //exit();
//}

// Handle Approve / Reject
if (isset($_GET['action'], $_GET['id'])) {
    $leave_id = intval($_GET['id']);
    $status = ($_GET['action'] === 'approve') ? 'Approved' : 'Rejected';

    $stmt = $conn->prepare("UPDATE leaves SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $leave_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to this page after action
    header("Location: manage_leaves.php");
    exit();
}

// Fetch all leave requests
$sql = "SELECT l.*, u.username FROM leaves l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC";
$leaves = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Leaves</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .back-btn {
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .back-btn i {
            margin-right: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        .pending { background: #ffc107; color: black; }
        .approved { background: #28a745; color: white; }
        .rejected { background: #dc3545; color: white; }
        .action-buttons a {
            padding: 6px 12px;
            border-radius: 15px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            margin: 0 5px;
        }
        .approve { background-color: #28a745; }
        .reject { background-color: #dc3545; }
        .action-buttons a:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body>

<a href="manager.php" class="back-btn"><i class="fas fa-arrow-left"></i>Back to Dashboard</a>

<h2>Leave Requests</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>From</th>
            <th>To</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $leaves->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['from_date'] ?></td>
                <td><?= $row['to_date'] ?></td>
                <td><?= htmlspecialchars($row['reason']) ?></td>
                <td>
                    <span class="status <?= strtolower($row['status']) ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                <td class="action-buttons">
                    <?php if ($row['status'] === 'Pending'): ?>
                        <a href="manage_leaves.php?action=approve&id=<?= $row['id'] ?>" class="approve">Approve</a>
                        <a href="manage_leaves.php?action=reject&id=<?= $row['id'] ?>" class="reject">Reject</a>

                    <?php else: ?>
                        <em>No Action</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>