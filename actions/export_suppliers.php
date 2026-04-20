<?php
require '../config/db.php';

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

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="suppliers_export.csv"');

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

fputcsv($output, ['SUPPLIER TRACKER SUPPLIERS REPORT']);
fputcsv($output, ['Generated On', date('F d, Y h:i A')]);
fputcsv($output, []);

writeSectionTitle($output, 'FILTER SUMMARY');
writeKeyValueRows($output, [
    'Search Keyword' => $search !== '' ? $search : 'None',
    'Category Filter' => $cat_filter !== '' ? $cat_filter : 'All Categories',
    'Supplier Name Filter' => $name_filter !== '' ? $name_filter : 'All Suppliers',
]);
fputcsv($output, []);

writeSectionTitle($output, 'REPORT SUMMARY');
writeKeyValueRows($output, [
    'Total Suppliers' => $totalSuppliers,
    'Categories Found' => !empty($categories) ? implode(', ', $categories) : 'None',
]);
fputcsv($output, []);

writeSectionTitle($output, 'SUPPLIER DETAILS');
fputcsv($output, ['No.', 'Vendor Name', 'Category', 'Contact Person', 'Email', 'Phone']);

foreach ($suppliers as $index => $supplier) {
    fputcsv($output, [
        $index + 1,
        $supplier['name'],
        $supplier['category'],
        $supplier['contact_person'],
        $supplier['email'],
        $supplier['phone'],
    ]);
}

fclose($output);
exit;
?>
