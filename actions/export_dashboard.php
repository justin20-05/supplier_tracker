<?php
require '../config/db.php';

// Fetch stats
$suppliersCount = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$ordersCount = $pdo->query("SELECT COUNT(*) FROM delivery_orders")->fetchColumn();
$totalRevenue = $pdo->query("
    SELECT SUM(oi.quantity * oi.unit_price_at_order)
    FROM order_items oi
    JOIN delivery_orders o ON oi.order_id = o.order_id
    WHERE LOWER(o.status) = 'received'
")->fetchColumn() ?: 0;

// Fetch recent products
$recentProducts = $pdo->query("SELECT p.product_name, s.name as supplier_name
                               FROM products p
                               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                               ORDER BY p.product_id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="dashboard_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write stats section
fputcsv($output, ['System Overview Stats']);
fputcsv($output, ['Metric', 'Value']);
fputcsv($output, ['Suppliers', $suppliersCount]);
fputcsv($output, ['Products', $productsCount]);
fputcsv($output, ['Orders', $ordersCount]);
fputcsv($output, ['Revenue (₱)', number_format($totalRevenue, 2)]);
fputcsv($output, []); // Empty row

// Write recent products section
fputcsv($output, ['Recent Added Products']);
fputcsv($output, ['Product Name', 'Supplier']);
foreach ($recentProducts as $product) {
    fputcsv($output, [
        $product['product_name'],
        $product['supplier_name'] ?? 'Unassigned'
    ]);
}

fclose($output);
exit;
?>