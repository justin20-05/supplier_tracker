<?php
require '../config/db.php';
include '../includes/header.php';

// Handle Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    try {
        $stmt->execute([$id]);
        header("Location: view_category.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $error = "Cannot delete category; it may be in use.";
    }
}

// NEW: Handle Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = $_POST['category_id'];
    $name = $_POST['category_name'];
    
    $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    if ($stmt->execute([$name, $id])) {
        header("Location: view_category.php?msg=updated");
        exit();
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <script>showToast("Category updated successfully!", "success");</script>
<?php endif; ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Supplier Categories</h2>
        <p class="text-gray-500 text-sm">Manage classifications for your delivery network</p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-center gap-6">
        <a href="../modules/supplier_list.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Suppliers
        </a>

        <a href="../actions/add_category.php"
            class="flex items-center justify-center px-5 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Category
        </a>
    </div>
</div>

<div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50/50 border-b border-gray-100">
                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Category Name</th>
                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($categories as $cat): ?>
            <tr class="group hover:bg-gray-50/50 transition-colors">
                <td class="px-8 py-5 font-bold text-gray-800"><?= htmlspecialchars($cat['category_name']) ?></td>
                <td class="px-8 py-5">
                    <div class="flex justify-end gap-2">
                        <button onclick="openEditModal(<?= $cat['category_id'] ?>, '<?= addslashes($cat['category_name']) ?>')" 
                                class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button onclick="confirmDelete(<?= $cat['category_id'] ?>)" 
                                class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="editModal" class="fixed inset-0 z-[150] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" onclick="closeEditModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <form method="POST" class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in duration-200">
            <input type="hidden" name="category_id" id="editCategoryId">
            <div class="p-10">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-2">Edit Category</h3>
                <p class="text-gray-500 text-sm mb-8">Update the name for this classification.</p>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Category Name</label>
                    <input type="text" name="category_name" id="editCategoryName" required
                        class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all">
                </div>

                <div class="flex gap-3 mt-10">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-4 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition uppercase tracking-widest text-xs">Cancel</button>
                    <button type="submit" name="update_category" class="flex-1 px-4 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100 uppercase tracking-widest text-xs">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-[150] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl p-10 text-center animate-in fade-in zoom-in duration-200">
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-xl font-black text-gray-900">Confirm Deletion</h3>
            <p class="text-gray-500 mt-2 text-sm">This action cannot be undone.</p>
            <div class="flex gap-3 mt-8">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</button>
                <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function confirmDelete(id) {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.href = `view_category.php?delete=${id}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>