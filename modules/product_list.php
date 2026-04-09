<?php
require '../config/db.php';   
include '../includes/header.php';

// --- FILTER HANDLING ---
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

// INTEGRATION: Change Order to product_id DESC to show recently added first
$query .= " ORDER BY p.product_id DESC";

$stmt = $pdo->prepare($query);

// Map unique placeholders to the search term to avoid PDO errors
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
            <select name="supplier_id" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white">
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
                <a href="product_list.php" class="flex-1 bg-gray-100 text-gray-600 py-2 rounded hover:bg-gray-200 transition text-sm font-bold text-center">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden border border-gray-100">
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
                <td class="p-4 text-center space-x-3">
                    <a href="../actions/edit_product.php?id=<?= $p['product_id'] ?>" 
                       class="text-blue-600 hover:text-blue-800 font-bold uppercase text-[10px] tracking-wider">Edit</a>
                    <button onclick="openDeleteModal('../actions/delete_product.php?id=<?= $p['product_id'] ?>')" 
                            class="text-red-500 hover:text-red-700 font-bold uppercase text-[10px] tracking-wider">Delete</button>
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
    <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full mx-4 transform transition-all">
        <div class="text-center">
            <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Confirm Deletion</h3>
            <p class="text-gray-500 mt-2 text-sm">Are you sure? This action cannot be undone and will permanently remove this item.</p>
        </div>
        <div class="flex gap-3 mt-6">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-2 bg-red-600 text-white text-center rounded-lg font-medium hover:bg-red-700 transition">Delete</a>
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