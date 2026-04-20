<?php
require '../config/db.php';

date_default_timezone_set('Asia/Manila');

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
$recentProducts = $pdo->query("SELECT p.product_name, s.name AS supplier_name
                               FROM products p
                               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                               ORDER BY p.product_id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="dashboard_export.csv"');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF");

function writeSectionTitle($handle, $title)
{
    fputcsv($handle, [$title]);
}

function writeKeyValueRows($handle, array $rows)
{
    foreach ($rows as $label => $value) {
        fputcsv($handle, [$label, $value]);
    }
}

fputcsv($output, ['SUPPLIER TRACKER DASHBOARD REPORT']);
fputcsv($output, ['Generated On', date('F d, Y h:i A')]);
fputcsv($output, []);

writeSectionTitle($output, 'OVERVIEW');
writeKeyValueRows($output, [
    'Total Suppliers' => $suppliersCount,
    'Total Products' => $productsCount,
    'Total Orders' => $ordersCount,
    'Total Revenue (PHP)' => number_format($totalRevenue, 2),
]);
fputcsv($output, []);

writeSectionTitle($output, 'RECENTLY ADDED PRODUCTS');
fputcsv($output, ['No.', 'Product Name', 'Supplier']);

foreach ($recentProducts as $index => $product) {
    fputcsv($output, [
        $index + 1,
        $product['product_name'],
        $product['supplier_name'] ?? 'Unassigned',
    ]);
}

fclose($output);
exit;
?>
