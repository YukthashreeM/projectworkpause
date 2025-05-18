<?php
$inputPassword = "your_actual_password"; // Replace with the password you are testing
$storedHash = "your_hashed_password_from_db"; // Copy the hashed password from your database

var_dump(password_verify($inputPassword, $storedHash));
?>