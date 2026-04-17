<?php
require '../config/db.php';
include '../includes/header.php';

$search   = $_GET['search'] ?? '';
$cat_filter = $_GET['category'] ?? '';
$name_filter = $_GET['name'] ?? '';

$hasFilters = $search || $cat_filter || $name_filter;

// Fetch unique categories and names for the dropdown filters
$categories = $pdo->query("SELECT category_name FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_COLUMN);
$names      = $pdo->query("SELECT DISTINCT name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

// Corrected Query: Uses alias 's', single ORDER BY, and sorts by newest first
$query = "SELECT s.* FROM suppliers s WHERE 1=1";
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

$query .= " GROUP BY s.supplier_id ORDER BY s.supplier_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Suppliers</h2>
        <p class="text-gray-500 text-sm">Manage your delivery network and categories</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="view_category.php"
            class="flex items-center justify-center px-5 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            View Categories
        </a>
        <a href="../actions/add_category.php"
            class="flex items-center justify-center px-5 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Category
        </a>
        <a href="../actions/add_supplier.php"
            class="flex items-center justify-center px-5 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Add Supplier
        </a>
    </div>
</div>

<form method="GET" action="supplier_list.php" class="bg-white p-4 rounded-lg shadow-sm border mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Keyword</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search..."
                class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Vendor Name</label>
            <select name="name" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white font-medium text-gray-700">
                <option value="">All Vendors</option>
                <?php foreach ($names as $n): ?>
                    <option value="<?= htmlspecialchars($n) ?>" <?= $name_filter == $n ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Category</label>
            <select name="category" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white font-medium text-gray-700">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $cat_filter == $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-gray-800 text-white py-2 rounded hover:bg-gray-700 transition text-sm font-bold">Apply Filter</button>
            <?php if ($hasFilters): ?>
                <a href="supplier_list.php" class="flex-1 bg-gray-100 text-gray-600 py-2 rounded hover:bg-gray-200 transition text-sm font-bold text-center border">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-widest">
                <th class="p-4 border-b">Vendor Name</th>
                <th class="p-4 border-b">Contact</th>
                <th class="p-4 border-b">Category</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($suppliers as $s): ?>
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-50 last:border-0">
                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="p-4 text-gray-500"><?= htmlspecialchars($s['email']) ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
                            <?= htmlspecialchars($s['category']) ?>
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center items-center gap-2">
                            <button onclick="viewSupplier(<?= htmlspecialchars(json_encode($s)) ?>)"
                                class="p-2 text-green-500 bg-green-50 rounded-lg hover:bg-green-600 hover:text-white transition-all inline-block mr-1" title="View Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <a href="../actions/edit_supplier.php?id=<?= $s['supplier_id'] ?>"
                                class="p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <button onclick="openDeleteModal('../actions/delete_supplier.php?id=<?= $s['supplier_id'] ?>')"
                                class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($suppliers)): ?>
                <tr>
                    <td colspan="4" class="p-12 text-center text-gray-400 italic font-medium">No suppliers match your criteria.</td>
                </tr>
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
        <p class="text-gray-500 mt-2 text-sm">Are you sure? This action cannot be undone and may affect linked products.</p>
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

<div id="viewModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <h3 id="modalTitle" class="text-2xl font-black text-gray-900">Supplier Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
            </div>
            <div id="modalContent" class="space-y-4">
            </div>
            <button onclick="closeModal()" class="w-full mt-8 py-4 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Close Preview</button>
        </div>
    </div>
</div>

<script>
    function viewSupplier(data) {
        const content = document.getElementById('modalContent');
        content.innerHTML = `
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="p-4 bg-gray-50 rounded-2xl"><p class="text-[10px] font-bold text-gray-400 uppercase">Supplier Name</p><p class="font-bold text-gray-800 text-lg">${data.name}</p></div>
            <div class="p-4 bg-gray-50 rounded-2xl"><p class="text-[10px] font-bold text-gray-400 uppercase">Category</p><p class="font-bold text-gray-800">${data.category}</p></div>
            <div class="p-4 bg-gray-50 rounded-2xl col-span-2"><p class="text-[10px] font-bold text-gray-400 uppercase">Contact Person</p><p class="font-bold text-gray-800">${data.contact_person}</p></div>
            <div class="p-4 bg-gray-50 rounded-2xl"><p class="text-[10px] font-bold text-gray-400 uppercase">Email</p><p class="font-bold text-gray-800">${data.email}</p></div>
            <div class="p-4 bg-gray-50 rounded-2xl"><p class="text-[10px] font-bold text-gray-400 uppercase">Phone</p><p class="font-bold text-gray-800">${data.phone}</p></div>
        </div>`;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>