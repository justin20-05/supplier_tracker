<?php
require '../config/db.php';
require 'export_excel_helpers.php';

date_default_timezone_set('Asia/Manila');

$search = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';
$name_filter = $_GET['name'] ?? '';

// Base query
$query = "SELECT s.name, s.category, s.contact_person, s.email, s.phone
          FROM suppliers s
          WHERE 1=1";

$params = [];

if ($search) {
    $query .= " AND (s.name LIKE ? OR s.category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cat_filter) {
    $query .= " AND s.category = ?";
    $params[] = $cat_filter;
}

if ($name_filter) {
    $query .= " AND s.name = ?";
    $params[] = $name_filter;
}

$query .= " ORDER BY s.supplier_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalSuppliers = count($suppliers);
$categories = array_unique(array_filter(array_map(static fn($supplier) => $supplier['category'] ?? '', $suppliers)));
sort($categories);

$filterRows = [];
foreach ([
    'Search Keyword' => $search !== '' ? $search : 'None',
    'Category Filter' => $cat_filter !== '' ? $cat_filter : 'All Categories',
    'Supplier Name Filter' => $name_filter !== '' ? $name_filter : 'All Suppliers',
] as $label => $value) {
    $filterRows[] = [$label, $value];
}

$summaryRows = [];
foreach ([
    'Total Suppliers' => $totalSuppliers,
    'Categories Found' => !empty($categories) ? implode(', ', $categories) : 'None',
] as $label => $value) {
    $summaryRows[] = [$label, $value];
}

$supplierRows = [];
foreach ($suppliers as $index => $supplier) {
    $supplierRows[] = [
        $index + 1,
        $supplier['name'],
        $supplier['category'],
        $supplier['contact_person'],
        $supplier['email'],
        $supplier['phone'],
    ];
}

outputExcelReport('suppliers_export.xls', 'SUPPLIER TRACKER SUPPLIERS REPORT', [
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
        'title' => 'SUPPLIER DETAILS',
        'colspan' => 6,
        'headers' => ['No.', 'Vendor Name', 'Category', 'Contact Person', 'Email', 'Phone'],
        'rows' => $supplierRows,
    ],
]);
?>
