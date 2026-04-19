<?php
require '../config/db.php';

if (isset($_GET['supplier_id'])) {
    $stmt = $pdo->prepare("SELECT product_id, product_name, unit_price, stocks FROM products WHERE supplier_id = ?");
    $stmt->execute([$_GET['supplier_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}