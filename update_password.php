<?php
require 'db.php'; // Ensure it connects to the database

$stmt = $pdo->query("SELECT username, password FROM Users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    if (!password_get_info($user['password'])['algo']) { // Check if password is already hashed
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE Users SET password = ? WHERE username = ?");
        $updateStmt->execute([$hashedPassword, $user['username']]);
    }
}

echo "Passwords updated successfully!";
?>