<?php
require '../config/db.php';

$order_id = $_GET['order_id'] ?? null;
if ($order_id) {
    $stmt = $pdo->prepare("SELECT oi.*, p.product_name 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.product_id 
                           WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}