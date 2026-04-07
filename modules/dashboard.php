<?php
require '../config/db.php';
include '../includes/header.php';

// Fetch counts for the Dashboard cards
$suppliersCount = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Fetch 5 most recently added products
$recentProducts = $pdo->query("SELECT p.product_name, s.name as supplier_name 
                               FROM products p 
                               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
                               ORDER BY p.product_id DESC LIMIT 5")->fetchAll();
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-gray-900 tracking-tight">System Overview</h1>
    <p class="text-gray-500">Logistics and Inventory Status</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
        <div class="text-blue-500 font-bold text-xs uppercase tracking-widest mb-2">Total Suppliers</div>
        <div class="text-4xl font-black text-gray-800"><?= $suppliersCount ?></div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
        <div class="text-green-500 font-bold text-xs uppercase tracking-widest mb-2">Total Products</div>
        <div class="text-4xl font-black text-gray-800"><?= $productsCount ?></div>
    </div>

    <a href="../actions/add_product.php" class="bg-blue-600 p-6 rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition group">
        <div class="text-blue-200 font-bold text-xs uppercase tracking-widest mb-2">Shortcuts</div>
        <div class="text-white font-bold text-xl flex items-center gap-2">
            Add Product <span class="group-hover:translate-x-1 transition">→</span>
        </div>
    </a>

    <a href="../actions/add_supplier.php" class="bg-gray-800 p-6 rounded-2xl shadow-lg shadow-gray-200 hover:bg-gray-900 transition group">
        <div class="text-gray-400 font-bold text-xs uppercase tracking-widest mb-2">Shortcuts</div>
        <div class="text-white font-bold text-xl flex items-center gap-2">
            New Supplier <span class="group-hover:translate-x-1 transition">→</span>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 bg-gray-50/50">
            <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Recently Added Items</h3>
        </div>
        <table class="w-full text-left">
            <tbody class="divide-y divide-gray-50">
                <?php foreach($recentProducts as $item): ?>
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="p-4">
                        <span class="block font-bold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="text-xs text-gray-400 italic"><?= htmlspecialchars($item['supplier_name'] ?? 'General') ?></span>
                    </td>
                    <td class="p-4 text-right">
                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-1 rounded">NEW</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center">
        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04kM12 21.75c-2.676 0-5.216-.584-7.499-1.632"></path></svg>
        </div>
        <h3 class="font-black text-gray-800 text-lg mb-2">Secure Access</h3>
        <p class="text-gray-500 text-sm leading-relaxed mb-6">You are logged in as an authorized manager. Your actions are logged for security purposes.</p>
        <a href="../modules/product_list.php" class="inline-block text-blue-600 font-bold border-b-2 border-blue-600 pb-1 hover:text-blue-800">Review Full Inventory</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>