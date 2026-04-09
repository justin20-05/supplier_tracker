<?php
require '../config/db.php';   
include '../includes/header.php';

$search = $_GET['search'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Fetch all suppliers for the dropdown
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC")->fetchAll();

// Base query using product_code as per your database schema
$query = "SELECT p.*, s.name as supplier_name 
          FROM products p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          WHERE (p.product_name LIKE :search1 OR p.product_code LIKE :search2)";

// Dynamic filtering logic
if (!empty($supplier_id)) { $query .= " AND p.supplier_id = :supplier_id"; }
if (!empty($min_price))    { $query .= " AND p.unit_price >= :min_price"; }
if (!empty($max_price))    { $query .= " AND p.unit_price <= :max_price"; }

// Change Order to product_id DESC to show recently added first
$query .= " ORDER BY p.product_id DESC";

$stmt = $pdo->prepare($query);

// Map unique placeholders
$params = [
    ':search1' => "%$search%",
    ':search2' => "%$search%"
];

if (!empty($supplier_id)) $params[':supplier_id'] = $supplier_id;
if (!empty($min_price))    $params[':min_price'] = $min_price;
if (!empty($max_price))    $params[':max_price'] = $max_price;

$stmt->execute($params);
$products = $stmt->fetchAll();

$hasFilters = $search || $supplier_id || $min_price || $max_price;
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

<form method="GET" action="product_list.php" class="bg-white p-4 rounded-lg shadow-sm border mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Keyword</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or code..."
                class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Supplier</label>
            <select name="supplier_id" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white font-medium text-gray-700">
                <option value="">All Suppliers</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['supplier_id'] ?>" <?= $supplier_id == $s['supplier_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Unit Price (₱)</label>
            <div class="flex items-center gap-2">
                <input type="number" name="min_price" value="<?= htmlspecialchars($min_price) ?>" placeholder="Min" 
                       class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                <span class="text-gray-300">-</span>
                <input type="number" name="max_price" value="<?= htmlspecialchars($max_price) ?>" placeholder="Max" 
                       class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-gray-800 text-white py-2 rounded hover:bg-gray-700 transition text-sm font-bold">Apply Filter</button>
            <?php if($hasFilters): ?>
                <a href="product_list.php" class="flex-1 bg-gray-100 text-gray-600 py-2 rounded hover:bg-gray-200 transition text-sm font-bold text-center border">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-widest">
                <th class="p-4 border-b">Product Code</th>
                <th class="p-4 border-b">Product Name</th>
                <th class="p-4 border-b">Supplier</th>
                <th class="p-4 border-b text-right">Unit Price</th>
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
                <td class="p-4 font-black text-gray-900 text-right">₱<?= number_format($p['unit_price'], 2) ?></td>
                <td class="p-4 text-center">
                    <div class="flex justify-center items-center gap-2">
                        <a href="../actions/edit_product.php?id=<?= $p['product_id'] ?>" 
                           class="p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </a>
                        <button onclick="openDeleteModal('../actions/delete_product.php?id=<?= $p['product_id'] ?>')"
                                class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr><td colspan="5" class="p-12 text-center text-gray-400 italic font-medium">No products match your search.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center">
        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900">Confirm Deletion</h3>
        <p class="text-gray-500 mt-2 text-sm">Are you sure? This action cannot be undone and will permanently remove this item from the inventory.</p>
        <div class="flex gap-3 mt-8">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 bg-red-600 text-white text-center rounded-2xl font-bold hover:bg-red-700 transition">Delete</a>
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