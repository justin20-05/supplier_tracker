<?php
require '../config/db.php';   
include '../includes/header.php';

$search = $_GET['search'] ?? '';

// Professional JOIN query to get Supplier Name along with Product info
$query = "SELECT p.*, s.name as supplier_name 
          FROM products p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          WHERE p.product_name LIKE ? OR p.sku LIKE ? 
          ORDER BY p.product_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%"]);
$products = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Product Inventory</h2>
    <a href="../actions/add_product.php" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition font-medium">+ New Product</a>
</div>

<form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
           placeholder="Search by product name or SKU..." 
           class="flex-1 p-2.5 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 outline-none">
    <button type="submit" class="bg-gray-800 text-white px-6 py-2.5 rounded-lg hover:bg-gray-700 transition font-bold">Search</button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-widest">
            <tr>
                <th class="p-4">SKU</th>
                <th class="p-4">Product Name</th>
                <th class="p-4">Supplier</th>
                <th class="p-4">Price</th>
                <th class="p-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
            <?php foreach ($products as $p): ?>
            <tr class="hover:bg-blue-50/20 transition">
                <td class="p-4 font-mono text-blue-600"><?= htmlspecialchars($p['sku']) ?></td>
                <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($p['product_name']) ?></td>
                <td class="p-4 text-gray-500 italic"><?= htmlspecialchars($p['supplier_name'] ?? 'No Supplier') ?></td>
                <td class="p-4 font-bold text-gray-900">₱<?= number_format($p['unit_price'], 2) ?></td>
                <td class="p-4 text-center space-x-3">
                    <a href="../actions/edit_product.php?id=<?= $p['product_id'] ?>" class="text-blue-500 hover:underline text-xs font-bold uppercase">Edit</a>
                    <button onclick="openDeleteModal('../actions/delete_product.php?id=<?= $p['product_id'] ?>')" class="text-red-400 hover:text-red-600">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr><td colspan="5" class="p-12 text-center text-gray-400 italic">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>