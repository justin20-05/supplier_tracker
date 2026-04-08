<?php
require '../config/db.php';   
include '../includes/header.php';

$search = $_GET['search'] ?? '';

// JOIN query 
$query = "SELECT p.*, s.name as supplier_name 
          FROM products p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          WHERE p.product_name LIKE ? OR p.product_code LIKE ? 
          ORDER BY p.product_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%"]);
$products = $stmt->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Product Inventory</h2>
        <p class="text-gray-500 text-sm">Monitor stock levels and unit pricing</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="../actions/add_product.php" 
           class="flex items-center justify-center px-5 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Product
        </a>
    </div>
</div>

<form method="GET" class="bg-white p-4 rounded-xl shadow-sm border mb-6 flex gap-3">
    <div class="flex-1">
        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Search Products</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
               placeholder="Search by name or Product Code..." 
               class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all">
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="px-8 bg-gray-800 text-white py-2.5 rounded-lg hover:bg-gray-700 transition text-sm font-bold">Search</button>
        <?php if($search): ?>
            <a href="product_list.php" class="px-4 bg-gray-100 text-gray-600 py-2.5 rounded-lg hover:bg-gray-200 transition text-sm font-bold text-center">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-widest">
                <th class="p-4 border-b">Product Code</th>
                <th class="p-4 border-b">Product Name</th>
                <th class="p-4 border-b">Supplier</th>
                <th class="p-4 border-b">Price</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($products as $p): ?>
            <tr class="hover:bg-blue-50/30 transition border-b border-gray-50 last:border-0">
                <td class="p-4 font-mono text-blue-600 font-bold"><?= htmlspecialchars($p['product_code']) ?></td>
                <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($p['product_name']) ?></td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
                        <?= htmlspecialchars($p['supplier_name'] ?? 'Unassigned') ?>
                    </span>
                </td>
                <td class="p-4 font-black text-gray-900">₱<?= number_format($p['unit_price'], 2) ?></td>
                <td class="p-4 text-center space-x-3">
                    <a href="../actions/edit_product.php?id=<?= $p['product_id'] ?>" 
                       class="text-blue-600 hover:text-blue-800 font-bold uppercase text-[10px] tracking-wider">Edit</a>
                    <button onclick="openDeleteModal('../actions/delete_product.php?id=<?= $p['product_id'] ?>')" 
                            class="text-red-400 hover:text-red-600 font-bold uppercase text-[10px] tracking-wider">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr><td colspan="5" class="p-12 text-center text-gray-400 italic font-medium">No products match your search.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-2xl shadow-2xl max-w-sm w-full mx-4 transform transition-all">
        <div class="text-center">
            <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Remove Product?</h3>
            <p class="text-gray-500 mt-2 text-sm">This will permanently delete the item from your inventory record.</p>
        </div>
        <div class="flex gap-3 mt-6">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-2.5 bg-red-600 text-white text-center rounded-xl font-bold hover:bg-red-700 transition">Delete</a>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(url) {
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>