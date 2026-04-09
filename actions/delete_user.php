<?php
require '../config/db.php';
session_start();

$id = $_GET['id'] ?? null;

// Prevent users from deleting themselves or unauthorized access
if ($_SESSION['role'] === 'Admin' && $id && $id != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    header("Location: ../modules/user_management.php?msg=deleted");
} else {
    header("Location: ../modules/user_management.php?err=unauthorized");
}
exit();