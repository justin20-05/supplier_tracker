<?php
require '../config/db.php';
require 'export_excel_helpers.php';

date_default_timezone_set('Asia/Manila');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

function isValidReportDate($value)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

function formatReportDate($value)
{
    return !empty($value) ? date('M d, Y', strtotime($value)) : 'N/A';
}

$defaultFromDate = date('Y-m-01');
$defaultToDate = date('Y-m-d');

$fromDate = isValidReportDate($_GET['from_date'] ?? '') ? $_GET['from_date'] : $defaultFromDate;
$toDate = isValidReportDate($_GET['to_date'] ?? '') ? $_GET['to_date'] : $defaultToDate;

if (strtotime($fromDate) > strtotime($toDate)) {
    [$fromDate, $toDate] = [$toDate, $fromDate];
}

$dateParams = [
    ':from_date' => $fromDate,
    ':to_date' => $toDate,
];
// CTE, AGGREGATION, JOIN QUERY
$statsStmt = $pdo->prepare("
    WITH report_scope AS (
        SELECT
            o.order_id,
            o.status
        FROM delivery_orders o
        WHERE o.expected_date BETWEEN :from_date AND :to_date
          AND LOWER(o.status) IN ('pending', 'received', 'cancelled')
    ),
    order_rollups AS (
        SELECT
            rs.order_id,
            rs.status,
            COALESCE(SUM(oi.quantity), 0) AS item_count,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) AS order_value
        FROM report_scope rs
        LEFT JOIN order_items oi ON rs.order_id = oi.order_id
        GROUP BY rs.order_id, rs.status
    ),
    report_totals AS (
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(CASE WHEN LOWER(status) = 'received' THEN 1 ELSE 0 END), 0) AS items_received,
            COALESCE(SUM(CASE WHEN LOWER(status) = 'received' THEN order_value ELSE 0 END), 0) AS total_expenditure
        FROM order_rollups
    )
    SELECT * FROM report_totals
");
$statsStmt->execute($dateParams);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$totalOrders = (int)($stats['total_orders'] ?? 0);
$itemsReceived = (int)($stats['items_received'] ?? 0);
$totalExpenditure = (float)($stats['total_expenditure'] ?? 0);
// CTE, AGGREGATION, JOIN QUERY
$performanceStmt = $pdo->prepare("
    WITH supplier_rollups AS (
        SELECT
            s.supplier_id,
            s.name AS supplier_name,
            o.order_id,
            o.expected_date,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) AS order_value
        FROM delivery_orders o
        INNER JOIN suppliers s ON o.supplier_id = s.supplier_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.expected_date BETWEEN :from_date AND :to_date
          AND LOWER(o.status) = 'received'
        GROUP BY s.supplier_id, s.name, o.order_id, o.expected_date
    )
    SELECT
        supplier_name,
        COUNT(order_id) AS order_count,
        COALESCE(SUM(order_value), 0) AS total_spent,
        MAX(expected_date) AS last_transaction_date
    FROM supplier_rollups
    GROUP BY supplier_id, supplier_name
    ORDER BY total_spent DESC, order_count DESC, supplier_name ASC
");
$performanceStmt->execute($dateParams);
$supplierPerformance = $performanceStmt->fetchAll(PDO::FETCH_ASSOC);

$averageOrderValue = $totalOrders > 0 ? $totalExpenditure / $totalOrders : 0;

$overviewRows = [];
foreach ([
    'Report Period' => formatReportDate($fromDate) . ' to ' . formatReportDate($toDate),
    'Total Orders' => number_format($totalOrders),
    'Items Received' => number_format($itemsReceived),
    'Total Expenditure (PHP)' => number_format($totalExpenditure, 2),
    'Average Order Value (PHP)' => number_format($averageOrderValue, 2),
] as $label => $value) {
    $overviewRows[] = [$label, $value];
}

$supplierRows = [];
foreach ($supplierPerformance as $supplier) {
    $supplierRows[] = [
        $supplier['supplier_name'] ?? 'Unknown Supplier',
        number_format((int)($supplier['order_count'] ?? 0)),
        'PHP ' . number_format((float)($supplier['total_spent'] ?? 0), 2),
        formatReportDate($supplier['last_transaction_date'] ?? ''),
    ];
}

$filename = 'reports_export_' . date('Y-m-d_H-i-s') . '.xls';
$title = 'SUPPLIER TRACKER REPORTS - ' . formatReportDate($fromDate) . ' to ' . formatReportDate($toDate);

outputExcelReport($filename, $title, [
    [
        'title' => 'OVERVIEW',
        'colspan' => 2,
        'headers' => [],
        'rows' => $overviewRows,
    ],
    [
        'title' => 'SUPPLIER PERFORMANCE',
        'colspan' => 4,
        'headers' => ['Supplier Name', 'Order Count', 'Total Spent', 'Last Transaction'],
        'rows' => $supplierRows,
    ],
]);
?>