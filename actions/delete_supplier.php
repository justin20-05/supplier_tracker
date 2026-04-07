<?php
require '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);

        header("Location: ../modules/supplier_list.php?msg=deleted");
        exit();
    } catch (PDOException $e) {

        die("Cannot delete: This supplier is linked to other records (like products).");
    }
} else {
    header("Location: ../modules/supplier_list.php");
    exit();
}
