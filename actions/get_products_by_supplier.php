<?php
require '../config/db.php';
$supplier_id = $_GET['supplier_id'] ?? 0;

$stmt = $pdo->prepare("SELECT product_id, product_name, unit_price, stock FROM products WHERE supplier_id = ?");
$stmt->execute([$supplier_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));