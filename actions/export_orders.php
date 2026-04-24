<?php
require '../config/db.php';
require 'export_excel_helpers.php';

date_default_timezone_set('Asia/Manila');

$supplier_filter = $_GET['supplier_id'] ?? '';
$status_filter = $_GET['status'] ?? '';

$supplierName = 'All Suppliers';
if (!empty($supplier_filter)) {
    $supplierStmt = $pdo->prepare("SELECT name FROM suppliers WHERE supplier_id = ?");
    $supplierStmt->execute([$supplier_filter]);
    $supplierName = $supplierStmt->fetchColumn() ?: 'Selected Supplier';
}

// Base query
$query = "SELECT
            o.order_id,
            s.name AS supplier_name,
            o.expected_date,
            o.status,
            COALESCE(SUM(oi.quantity), 0) AS total_quantity,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) AS total_order_value
          FROM delivery_orders o
          LEFT JOIN suppliers s ON o.supplier_id = s.supplier_id
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          WHERE 1=1";

$params = [];

if (!empty($supplier_filter)) {
    $query .= " AND o.supplier_id = ?";
    $params[] = $supplier_filter;
}

if (!empty($status_filter)) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

$query .= " GROUP BY o.order_id, s.name, o.expected_date, o.status ORDER BY o.order_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalOrders = count($orders);
$totalQuantity = array_sum(array_map(static fn($order) => (float) $order['total_quantity'], $orders));
$totalValue = array_sum(array_map(static fn($order) => (float) $order['total_order_value'], $orders));

$filterRows = [];
foreach ([
    'Supplier Filter' => $supplierName,
    'Status Filter' => $status_filter !== '' ? $status_filter : 'All Statuses',
] as $label => $value) {
    $filterRows[] = [$label, $value];
}

$summaryRows = [];
foreach ([
    'Total Orders' => $totalOrders,
    'Total Quantity' => number_format($totalQuantity, 0),
    'Total Order Value (PHP)' => number_format($totalValue, 2),
] as $label => $value) {
    $summaryRows[] = [$label, $value];
}

$orderRows = [];
foreach ($orders as $index => $order) {
    $orderRows[] = [
        $index + 1,
        'ORD-' . $order['order_id'],
        $order['supplier_name'] ?? 'N/A',
        !empty($order['expected_date']) ? date('M d, Y', strtotime($order['expected_date'])) : 'N/A',
        $order['status'],
        $order['total_quantity'],
        number_format($order['total_order_value'], 2),
    ];
}

outputExcelReport('orders_export.xls', 'SUPPLIER TRACKER ORDERS REPORT', [
    [
        'title' => 'FILTER SUMMARY',
        'colspan' => 2,
        'headers' => [],
        'rows' => $filterRows,
    ],
    [
        'title' => 'REPORT SUMMARY',
        'colspan' => 2,
        'headers' => [],
        'rows' => $summaryRows,
    ],
    [
        'title' => 'ORDER DETAILS',
        'colspan' => 7,
        'headers' => ['No.', 'Order ID', 'Supplier', 'Expected Date', 'Status', 'Total Quantity', 'Total Value (PHP)'],
        'rows' => $orderRows,
    ],
]);
?>
