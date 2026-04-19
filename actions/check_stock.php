<?php
require '../config/db.php';
header('Content-Type: application/json');

try {
    // Query to select items with 10 or less stock
    $stmt = $pdo->query("SELECT product_name, stock FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 5");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($items);
} catch (PDOException $e) {
    echo json_encode([]);
}