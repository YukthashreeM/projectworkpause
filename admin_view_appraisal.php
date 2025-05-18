<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "workpausedb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM appraisal";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Appraisal Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f2f2f2;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <h2>Employee Appraisal Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Appraisal Score</th>
            <th>Salary Increment</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['employee_id']}</td>
                        <td>{$row['employee_name']}</td>
                        <td>{$row['appraisal_score']}</td>
                        <td>₹{$row['salary_increment']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No appraisal records found.</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>