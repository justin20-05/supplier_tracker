<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? null;

// Security Check: Only Admin can delete
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../modules/user_management.php?err=unauthorized");
    exit();
}

// Safety Check: Cannot delete yourself
if ($id == $_SESSION['user_id']) {
    header("Location: ../modules/user_management.php?err=self_delete");
    exit();
}

// Execution
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: ../modules/user_management.php?msg=deleted");
        } else {
            header("Location: ../modules/user_management.php?err=not_found");
        }
    } catch (PDOException $e) {
        header("Location: ../modules/user_management.php?err=db_error");
    }
} else {
    header("Location: ../modules/user_management.php");
}
exit();