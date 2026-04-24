<?php
require '../config/db.php';
require 'export_excel_helpers.php';

date_default_timezone_set('Asia/Manila');

$search = $_GET['search'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$supplierName = 'All Suppliers';
if (!empty($supplier_id)) {
    $supplierStmt = $pdo->prepare("SELECT name FROM suppliers WHERE supplier_id = ?");
    $supplierStmt->execute([$supplier_id]);
    $supplierName = $supplierStmt->fetchColumn() ?: 'Selected Supplier';
}

// Base query
$query = "SELECT p.product_code, p.product_name, s.name AS supplier_name, p.stock, p.unit_price
          FROM products p
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
          WHERE 1=1";

$params = [];

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

$totalProducts = count($products);
$totalStock = array_sum(array_map(static fn($product) => (float) $product['stock'], $products));
$totalInventoryValue = array_sum(array_map(
    static fn($product) => (float) $product['stock'] * (float) $product['unit_price'],
    $products
));

$filterRows = [];
foreach ([
    'Search Keyword' => $search !== '' ? $search : 'None',
    'Supplier Filter' => $supplierName,
    'Minimum Price' => $min_price !== '' ? number_format((float) $min_price, 2) : 'None',
    'Maximum Price' => $max_price !== '' ? number_format((float) $max_price, 2) : 'None',
] as $label => $value) {
    $filterRows[] = [$label, $value];
}

$summaryRows = [];
foreach ([
    'Total Products' => $totalProducts,
    'Total Stock' => number_format($totalStock, 0),
    'Estimated Inventory Value (PHP)' => number_format($totalInventoryValue, 2),
] as $label => $value) {
    $summaryRows[] = [$label, $value];
}

$productRows = [];
foreach ($products as $index => $product) {
    $productRows[] = [
        $index + 1,
        $product['product_code'],
        $product['product_name'],
        $product['supplier_name'] ?? 'Unassigned',
        $product['stock'],
        number_format($product['unit_price'], 2),
    ];
}

outputExcelReport('products_export.xls', 'SUPPLIER TRACKER PRODUCTS REPORT', [
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
        'title' => 'PRODUCT DETAILS',
        'colspan' => 6,
        'headers' => ['No.', 'Product Code', 'Product Name', 'Supplier', 'Stock', 'Unit Price (PHP)'],
        'rows' => $productRows,
    ],
]);
?>
