<?php
// Connect to DB
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "workpausedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to add/update payroll basic salary
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_id = $_POST["employee_id"];
    $basic_salary = floatval($_POST["basic_salary"]);

    // Check if payroll entry exists for employee
    $check = $conn->prepare("SELECT id FROM payroll WHERE employee_id = ?");
    $check->bind_param("i", $employee_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update existing
        $update = $conn->prepare("UPDATE payroll SET basic_salary = ? WHERE employee_id = ?");
        $update->bind_param("di", $basic_salary, $employee_id);
        $update->execute();
        $update->close();
    } else {
        // Insert new
        $insert = $conn->prepare("INSERT INTO payroll (employee_id, basic_salary) VALUES (?, ?)");
        $insert->bind_param("id", $employee_id, $basic_salary);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// Fetch payroll data with user info
$sql = "SELECT p.id, p.employee_id, p.basic_salary, u.username 
        FROM payroll p 
        JOIN users u ON p.employee_id = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f6ff;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            width: 40%;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 1rem;
        }
        form input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        form input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
            font-size: 0.9rem;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<h2>Payroll Entry Form</h2>
<form method="post" action="">
    <label for="employee_id">Select Employee:</label>
    <select name="employee_id" id="employee_id" required>
        <option value="">--Select Employee--</option>
        <?php
        // Populate dropdown with all employees (users with role Employee)
        $empResult = $conn->query("SELECT id, username FROM users");
        while ($emp = $empResult->fetch_assoc()) {
            echo "<option value='{$emp['id']}'>" . htmlspecialchars($emp['username']) . "</option>";
        }
        ?>
    </select>

    <label for="basic_salary">Basic Salary (INR):</label>
    <input type="number" step="0.01" name="basic_salary" id="basic_salary" required placeholder="Enter basic salary">

    <input type="submit" value="Save Payroll">
</form>

<h2>Payroll Overview</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Employee Name</th>
        <th>Basic Salary (INR)</th>
        <th>HRA (20%)</th>
        <th>DA (10%)</th>
        <th>CCA (5%)</th>
        <th>IR (2%)</th>
        <th>Other Allowances (3%)</th>
        <th>PT (₹200)</th>
        <th>PF (12% max ₹2500)</th>
        <th>Other Deductions (₹0)</th>
        <th>Gross Salary</th>
        <th>Net Salary</th>
    </tr>

    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $basic_salary = floatval($row['basic_salary']);
            $hra = 0.20 * $basic_salary;
            $da = 0.10 * $basic_salary;
            $cca = 0.05 * $basic_salary;
            $ir = 0.02 * $basic_salary;
            $other_allowances = 0.03 * $basic_salary;

            $pt = 200;
            $pf = min(0.12 * $basic_salary, 2500);
            $other_deductions = 0;

            $gross_salary = $basic_salary + $hra + $da + $cca + $ir + $other_allowances;
            $total_deductions = $pt + $pf + $other_deductions;
            $net_salary = $gross_salary - $total_deductions;

            echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['username']) . "</td>
                <td>" . number_format($basic_salary, 2) . "</td>
                <td>" . number_format($hra, 2) . "</td>
                <td>" . number_format($da, 2) . "</td>
                <td>" . number_format($cca, 2) . "</td>
                <td>" . number_format($ir, 2) . "</td>
                <td>" . number_format($other_allowances, 2) . "</td>
                <td>" . number_format($pt, 2) . "</td>
                <td>" . number_format($pf, 2) . "</td>
                <td>" . number_format($other_deductions, 2) . "</td>
                <td>" . number_format($gross_salary, 2) . "</td>
                <td>" . number_format($net_salary, 2) . "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='13'>No payroll data found.</td></tr>";
    }
    ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
