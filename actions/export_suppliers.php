<?php
require '../config/db.php';

$search = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';
$name_filter = $_GET['name'] ?? '';

// Base query
$query = "SELECT s.name, s.category, s.contact_person, s.email, s.phone FROM suppliers s WHERE 1=1";

$params = [];

// Filters
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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="suppliers_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Vendor Name', 'Category', 'Contact Person', 'Email', 'Phone']);

// Write data
foreach ($suppliers as $supplier) {
    fputcsv($output, [
        $supplier['name'],
        $supplier['category'],
        $supplier['contact_person'],
        $supplier['email'],
        $supplier['phone']
    ]);
}

fclose($output);
exit;
?>