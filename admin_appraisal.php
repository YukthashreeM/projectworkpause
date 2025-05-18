<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "workpausedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle appraisal entry
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST["employee_id"];
    
    // Fetch the employee name based on selected ID
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($employee_name);
    $stmt->fetch();
    $stmt->close();

    $appraisal_score = $_POST["appraisal_score"];

    // Calculate salary increment
    if ($appraisal_score >= 90) {
        $salary_increment = 10000;
    } elseif ($appraisal_score >= 75) {
        $salary_increment = 5000;
    } else {
        $salary_increment = 2000;
    }

    $stmt = $conn->prepare("INSERT INTO appraisal (employee_id, employee_name, appraisal_score, salary_increment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isid", $employee_id, $employee_name, $appraisal_score, $salary_increment);

    if ($stmt->execute()) {
        $message = "✅ Appraisal data added successfully!";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all users for dropdown
$employees = [];
$result = $conn->query("SELECT id, username FROM users");
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Appraisal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            display: flex;
            justify-content: center;
            padding: 50px 20px;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 35px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 420px;
            max-width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #222;
            font-weight: 700;
            letter-spacing: 1px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }
        select, input[type="submit"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1.8px solid #ccc;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        select:focus, input[type="range"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 6px #007bff;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            font-weight: 700;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .range-container {
            position: relative;
            margin-bottom: 25px;
        }
        input[type="range"] {
            -webkit-appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #ddd;
            cursor: pointer;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 22px;
            height: 22px;
            background: #007bff;
            cursor: pointer;
            border-radius: 50%;
            border: 2px solid #0056b3;
            margin-top: -7px;
            transition: background-color 0.3s ease;
        }
        input[type="range"]:hover::-webkit-slider-thumb {
            background-color: #0056b3;
        }
        .score-label {
            font-weight: 700;
            color: #333;
            text-align: right;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        .message {
            margin-top: 15px;
            font-weight: 600;
            text-align: center;
            font-size: 1rem;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Employee Appraisal</h2>
    <form method="post" novalidate>
        <label for="employee_id">Select Employee:</label>
        <select name="employee_id" id="employee_id" required>
            <option value="" disabled selected>-- Select Employee --</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?= htmlspecialchars($emp['id']) ?>">
                    <?= htmlspecialchars($emp['username']) ?> (ID: <?= htmlspecialchars($emp['id']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <div class="range-container">
            <label for="appraisal_score" class="score-label">Appraisal Score: <span id="scoreValue">50</span></label>
            <input
                type="range"
                name="appraisal_score"
                id="appraisal_score"
                min="0" max="100"
                value="50"
                required
                oninput="document.getElementById('scoreValue').textContent = this.value"
            >
        </div>

        <input type="submit" value="Submit Appraisal">
    </form>

    <?php if (!empty($message)): ?>
        <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Initialize score label on page load
    document.addEventListener('DOMContentLoaded', () => {
        const range = document.getElementById('appraisal_score');
        const scoreLabel = document.getElementById('scoreValue');
        scoreLabel.textContent = range.value;
    });
</script>
</body>
</html>
