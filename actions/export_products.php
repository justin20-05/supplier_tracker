<?php
require '../config/db.php';

$search = $_GET['search'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Base query
$query = "SELECT p.product_code, p.product_name, s.name as supplier_name, p.stock, p.unit_price
          FROM products p
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
          WHERE 1=1";

$params = [];

// Filters
if (!empty($search)) {
    $query .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($supplier_id)) {
    $query .= " AND p.supplier_id = :supplier_id";
    $params[':supplier_id'] = $supplier_id;
}

if (!empty($min_price)) {
    $query .= " AND p.unit_price >= :min_price";
    $params[':min_price'] = $min_price;
}

if (!empty($max_price)) {
    $query .= " AND p.unit_price <= :max_price";
    $params[':max_price'] = $max_price;
}

$query .= " ORDER BY p.product_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Product Code', 'Product Name', 'Supplier', 'Stock', 'Unit Price (₱)']);

// Write data
foreach ($products as $product) {
    fputcsv($output, [
        $product['product_code'],
        $product['product_name'],
        $product['supplier_name'] ?? 'Unassigned',
        $product['stock'],
        number_format($product['unit_price'], 2)
    ]);
}

fclose($output);
exit;
?>