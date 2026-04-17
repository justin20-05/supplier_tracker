<?php
require '../config/db.php';
require '../includes/pagination.php';
include '../includes/header.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Fetch suppliers dropdown
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Base queries
$query = "SELECT p.*, s.name as supplier_name 
          FROM products p 
          LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
          WHERE 1=1";

$countQuery = "SELECT COUNT(*) 
               FROM products p 
               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
               WHERE 1=1";

$params = [];

//  filters 
if (!empty($search)) {
    $query .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search)";
    $countQuery .= " AND (p.product_name LIKE :search OR p.product_code LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($supplier_id)) {
    $query .= " AND p.supplier_id = :supplier_id";
    $countQuery .= " AND p.supplier_id = :supplier_id";
    $params[':supplier_id'] = $supplier_id;
}

if (!empty($min_price)) {
    $query .= " AND p.unit_price >= :min_price";
    $countQuery .= " AND p.unit_price >= :min_price";
    $params[':min_price'] = $min_price;
}

if (!empty($max_price)) {
    $query .= " AND p.unit_price <= :max_price";
    $countQuery .= " AND p.unit_price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Get total rows 
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Final query 
$query .= " ORDER BY p.product_id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
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
            <?php if ($hasFilters): ?>
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
                            <button onclick="viewProduct(<?= htmlspecialchars(json_encode($p)) ?>)"
                                class="p-2 text-green-500 bg-green-50 rounded-lg hover:bg-green-600 hover:text-white transition-all inline-block mr-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <a href="../actions/edit_product.php?id=<?= $p['product_id'] ?>"
                                class="p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <button onclick="openDeleteModal('../actions/delete_product.php?id=<?= $p['product_id'] ?>')"
                                class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" class="p-12 text-center text-gray-400 italic font-medium">No products match your search.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php renderPagination($page, $totalPages, $totalRows, $limit); ?>

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

<div id="viewModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-2xl font-black text-gray-900">Product Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modalContent" class="space-y-4">
            </div>
            <button onclick="closeModal()" class="w-full mt-8 py-4 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Close Preview</button>
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

    function viewProduct(data) {
        const content = document.getElementById('modalContent');
        content.innerHTML = `
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="p-4 bg-gray-50 rounded-2xl col-span-2">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Product Name</p>
                <p class="font-bold text-gray-800 text-lg">${data.product_name}</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-2xl">
                <p class="text-[10px] font-bold text-blue-400 uppercase">Product Code</p>
                <p class="font-mono font-bold text-blue-800">${data.product_code}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Category</p>
                <p class="font-bold text-gray-800">${data.category || 'Uncategorized'}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl col-span-2">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Supplier</p>
                <p class="font-bold text-gray-800">${data.supplier_name || 'N/A'}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Unit Price</p>
                <p class="font-bold text-gray-900 text-lg">₱${parseFloat(data.unit_price).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
            </div>
            <div class="p-4 ${data.stock < 10 ? 'bg-red-50' : 'bg-green-50'} rounded-2xl">
                <p class="text-[10px] font-bold ${data.stock < 10 ? 'text-red-400' : 'text-green-400'} uppercase">Stock Level</p>
                <p class="font-bold ${data.stock < 10 ? 'text-red-700' : 'text-green-700'} text-lg">${data.stock} units</p>
            </div>
        </div>`;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>