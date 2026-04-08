<?php
require '../config/db.php';
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // We delete order_items first because of the foreign key constraint
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM delivery_orders WHERE order_id = ?");
        $stmt->execute([$id]);

        header("Location: ../modules/order_list.php?msg=deleted");
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>