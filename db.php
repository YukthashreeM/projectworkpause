<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=WorkPauseDB;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // echo "Database connected successfully!"; // Optional for testing
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>


