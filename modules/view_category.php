<?php
require '../config/db.php';
include '../includes/header.php';

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

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Supplier Categories</h2>
        <p class="text-gray-500 text-sm">Manage classifications for your delivery network</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="../modules/supplier_list.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition flex items-center mr-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
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

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 font-bold flex items-center animate-fade-in-down">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        Category deleted successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6 font-bold flex items-center animate-bounce">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        Cannot delete: This category is currently being used by a supplier.
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-widest">
                <th class="p-4 border-b">Category Name</th>
                <th class="p-4 border-b">Created At</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td class="p-4 text-gray-500"><?= date('M d, Y', strtotime($cat['created_at'])) ?></td>
                    <td class="p-4 text-center">
                        <button onclick="confirmDelete(<?= $cat['category_id'] ?>)"
                            class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all inline-block" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="deleteModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all">
        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900">Confirm Deletion</h3>
        <p class="text-gray-500 mt-2 text-sm leading-relaxed">Are you sure you want to delete this category? This action cannot be undone and may affect linked suppliers.</p>
        <div class="flex gap-3 mt-8">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">Delete</a>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        const modal = document.getElementById('deleteModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        confirmBtn.href = `../actions/delete_category.php?id=${id}`;
        
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            closeDeleteModal();
        }
    }
</script>

<?php include '../includes/footer.php'; ?>