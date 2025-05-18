<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cab Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
            margin: auto;
            max-width: 450px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .success {
            background-color: #d4edda;
            padding: 10px;
            margin-top: 15px;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Cab Booking</h2>
    <form method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="pickup">Pickup Location:</label>
        <input type="text" name="pickup" id="pickup" required>

        <label for="drop">Drop Location:</label>
        <input type="text" name="drop" id="drop" required>

        <label for="cab_type">Cab Type:</label>
        <select name="cab_type" id="cab_type" required>
            <option value="">--Select--</option>
            <option value="Mini">Mini</option>
            <option value="Sedan">Sedan</option>
            <option value="SUV">SUV</option>
        </select>

        <button type="submit">Book Now</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // DB credentials
        $server = "localhost";
        $username = "root";
        $password = ""; // your DB password
        $database = "workpauseDB"; // your DB name

        // Connect to DB
        $conn = new mysqli($server, $username, $password, $database);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Sanitize and capture inputs
        $name = htmlspecialchars($_POST['name']);
        $pickup = htmlspecialchars($_POST['pickup']);
        $drop = htmlspecialchars($_POST['drop']);
        $cab_type = htmlspecialchars($_POST['cab_type']);

        // Insert into DB
        $sql = "INSERT INTO cab_bookings (name, pickup_location, drop_location, cab_type)
                VALUES ('$name', '$pickup', '$drop', '$cab_type')";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='success'>";
            echo "<strong>Booking Successful!</strong><br>";
            echo "Thanks, <b>$name</b>. Your cab is booked.<br>";
            echo "Pickup: $pickup<br>Drop: $drop<br>Cab: $cab_type";
            echo "</div>";
        } else {
            echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
        }

        $conn->close();
    }
    ?>
</div>
</body>
</html>
