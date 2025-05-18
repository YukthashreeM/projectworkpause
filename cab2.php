<?php
// Database connection details
$server = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$database = "workpauseDB"; // Replace with your database name

// Create a connection to the database
$conn = new mysqli($server, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch booking records
$sql = "SELECT * FROM cab_bookings ORDER BY booking_date DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #5cb85c;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>All Cab Bookings</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Pickup Location</th>
                <th>Drop Location</th>
                <th>Cab Type</th>
                <th>Booking Date</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['pickup_location']}</td>
                            <td>{$row['drop_location']}</td>
                            <td>{$row['cab_type']}</td>
                            <td>{$row['booking_date']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No bookings found</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
<?php
// Close the connection
$conn->close();
?>