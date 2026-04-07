<?php
require '../config/db.php';    
include '../includes/header.php'; 

// Get filter values from the URL
$search   = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';
$name_filter = $_GET['name'] ?? '';

// Fetch unique categories and names for the dropdown filters
$categories = $pdo->query("SELECT DISTINCT category FROM suppliers ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
$names      = $pdo->query("SELECT DISTINCT name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

//  Dynamic Query
$query = "SELECT * FROM suppliers WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cat_filter) {
    $query .= " AND category = ?";
    $params[] = $cat_filter;
}

if ($name_filter) {
    $query .= " AND name = ?";
    $params[] = $name_filter;
}

$query .= " ORDER BY name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Suppliers</h2>
    <a href="../actions/add_supplier.php" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">+ Add Supplier</a>
</div>

<form method="GET" action="supplier_list.php" class="bg-white p-4 rounded-lg shadow-sm border mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Keyword</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..."
                class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Vendor Name</label>
            <select name="name" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white">
                <option value="">All Vendors</option>
                <?php foreach ($names as $n): ?>
                    <option value="<?= htmlspecialchars($n) ?>" <?= $name_filter == $n ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Category</label>
            <select name="category" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $cat_filter == $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-gray-800 text-white py-2 rounded hover:bg-gray-700 transition text-sm font-bold">Apply</button>
            <a href="../modules/supplier_list.php" class="flex-1 bg-gray-100 text-gray-600 py-2 rounded hover:bg-gray-200 transition text-sm font-bold text-center">Reset</a>
        </div>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-bold">
                <th class="p-4 border-b">Vendor Name</th>
                <th class="p-4 border-b">Contact</th>
                <th class="p-4 border-b">Category</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($suppliers as $s): ?>
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="p-4 border-b font-semibold text-gray-800"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="p-4 border-b text-gray-500"><?= htmlspecialchars($s['email']) ?></td>
                    <td class="p-4 border-b">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
                            <?= htmlspecialchars($s['category']) ?>
                        </span>
                    </td>
                    <td class="p-4 border-b text-center space-x-3">
                        <a href="../actions/edit_supplier.php?id=<?= $s['supplier_id'] ?>" class="text-blue-600 hover:text-blue-800">Edit</a>
                        <a href="../actions/delete_supplier.php?id=<?= $s['supplier_id'] ?>" onclick="return confirm(...)">Delete</a>
                        <button onclick="openDeleteModal('../actions/delete_supplier.php?id=<?= $s['supplier_id'] ?>')"
                            class="text-red-500 hover:text-red-700 font-medium">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
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
            <p class="text-gray-500 mt-2">Are you sure? This action cannot be undone and may affect linked products.</p>
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