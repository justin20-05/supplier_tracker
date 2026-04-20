<?php
require '../config/db.php';

$supplier_filter = $_GET['supplier_id'] ?? '';
$status_filter = $_GET['status'] ?? '';

// BASE QUERY
$query = "SELECT
            o.order_id,
            s.name as supplier_name,
            o.expected_date,
            o.status,
            COALESCE(SUM(oi.quantity), 0) as total_quantity,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) as total_order_value
          FROM delivery_orders o
          LEFT JOIN suppliers s ON o.supplier_id = s.supplier_id
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          WHERE 1=1";

$params = [];

// Filters
if (!empty($supplier_filter)) {
    $query .= " AND o.supplier_id = ?";
    $params[] = $supplier_filter;
}

if (!empty($status_filter)) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

$query .= " GROUP BY o.order_id ORDER BY o.order_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Order ID', 'Supplier', 'Expected Date', 'Status', 'Total Quantity', 'Total Value (₱)']);

// Write data
foreach ($orders as $order) {
    fputcsv($output, [
        'ORD-' . $order['order_id'],
        $order['supplier_name'] ?? 'N/A',
        date('M d, Y', strtotime($order['expected_date'])),
        $order['status'],
        $order['total_quantity'],
        number_format($order['total_order_value'], 2)
    ]);
}

fclose($output);
exit;
?>