<?php
require '../config/db.php';
require 'export_excel_helpers.php';

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

$overviewRows = [];
foreach ([
    'Total Suppliers' => $suppliersCount,
    'Total Products' => $productsCount,
    'Total Orders' => $ordersCount,
    'Total Revenue (PHP)' => number_format($totalRevenue, 2),
] as $label => $value) {
    $overviewRows[] = [$label, $value];
}

$recentProductRows = [];
foreach ($recentProducts as $index => $product) {
    $recentProductRows[] = [
        $index + 1,
        $product['product_name'],
        $product['supplier_name'] ?? 'Unassigned',
    ];
}

outputExcelReport('dashboard_export.xls', 'SUPPLIER TRACKER DASHBOARD REPORT', [
    [
        'title' => 'OVERVIEW',
        'colspan' => 2,
        'headers' => [],
        'rows' => $overviewRows,
    ],
    [
        'title' => 'RECENTLY ADDED PRODUCTS',
        'colspan' => 3,
        'headers' => ['No.', 'Product Name', 'Supplier'],
        'rows' => $recentProductRows,
    ],
]);
?>
