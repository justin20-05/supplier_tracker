<?php
require '../config/db.php';
session_start();

// Security: Check login
if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // Redirect back to product list with success message
        header("Location: ../modules/product_list.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        die("Error deleting product: " . $e->getMessage());
    }
} else {
    header("Location: ../modules/product_list.php"); 
    exit();
}
?>