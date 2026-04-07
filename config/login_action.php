<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $user_data = $stmt->fetch();

    if (!$user_data) {
        die("Error: Username '$user' not found in the database.");
    }

    if (password_verify($pass, $user_data['password'])) {
        $_SESSION['user_id'] = $user_data['user_id'];
        $_SESSION['role'] = $user_data['role'];
        header("Location: ../modules/dashboard.php");
        exit();
    } else {
        die("Error: Password check failed. The hash in the DB does not match 'admin123'.");
    }
}
?>