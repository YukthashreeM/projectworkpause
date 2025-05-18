<?php
$servername = "localhost"; // Change if using a different host
$username = "root"; // Default XAMPP user
$password = ""; // Default XAMPP has no password
$database ="WorkPauseDB"; // Rplace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>