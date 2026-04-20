<?php
require '../config/db.php';

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

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="products_export.csv"');

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

fputcsv($output, ['SUPPLIER TRACKER PRODUCTS REPORT']);
fputcsv($output, ['Generated On', date('F d, Y h:i A')]);
fputcsv($output, []);

writeSectionTitle($output, 'FILTER SUMMARY');
writeKeyValueRows($output, [
    'Search Keyword' => $search !== '' ? $search : 'None',
    'Supplier Filter' => $supplierName,
    'Minimum Price' => $min_price !== '' ? number_format((float) $min_price, 2) : 'None',
    'Maximum Price' => $max_price !== '' ? number_format((float) $max_price, 2) : 'None',
]);
fputcsv($output, []);

writeSectionTitle($output, 'REPORT SUMMARY');
writeKeyValueRows($output, [
    'Total Products' => $totalProducts,
    'Total Stock' => number_format($totalStock, 0),
    'Estimated Inventory Value (PHP)' => number_format($totalInventoryValue, 2),
]);
fputcsv($output, []);

writeSectionTitle($output, 'PRODUCT DETAILS');
fputcsv($output, ['No.', 'Product Code', 'Product Name', 'Supplier', 'Stock', 'Unit Price (PHP)']);

foreach ($products as $index => $product) {
    fputcsv($output, [
        $index + 1,
        $product['product_code'],
        $product['product_name'],
        $product['supplier_name'] ?? 'Unassigned',
        $product['stock'],
        number_format($product['unit_price'], 2),
    ]);
}

fclose($output);
exit;
?>
