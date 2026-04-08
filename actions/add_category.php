<?php
require '../config/db.php';
include '../includes/header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cat_name = trim($_POST['category_name']);

    if (!empty($cat_name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        try {
            $stmt->execute([$cat_name]);
            $message = "<p class='text-green-600 font-bold'>Category '$cat_name' added successfully!</p>";
        } catch (PDOException $e) {
            $message = "<p class='text-red-500'>Error: Category might already exist.</p>";
        }
    }
}
?>

<div class="max-w-lg mx-auto bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mt-10">
    <h2 class="text-2xl font-black text-gray-800 mb-6">Add New Category</h2>
    
    <?= $message ?>

    <form method="POST" class="space-y-4 mt-4">
        <div>
            <a href="../modules/supplier_list.php" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">← Back to List</a>
            </div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Category Name</label>
            <input type="text" name="category_name" placeholder="e.g. Packaging, Chemicals" 
                   class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none" required>
        </div>
        
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition">Save Category</button>
            <a href="../modules/supplier_list.php" class="flex-1 text-center py-4 text-gray-500 font-bold hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>