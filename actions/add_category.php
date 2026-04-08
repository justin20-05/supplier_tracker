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
            $message = "<div class='bg-green-100 text-green-700 p-4 rounded-2xl mb-6 font-bold flex items-center'>
                            <svg class='w-5 h-5 mr-2' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'></path></svg>
                            Category '$cat_name' added successfully!
                        </div>";
        } catch (PDOException $e) {
            $message = "<div class='bg-red-100 text-red-700 p-4 rounded-2xl mb-6 font-bold'>Error: Category already exists.</div>";
        }
    }
}
?>

<div class="max-w-xl mx-auto mt-10">
    <div class="mb-6 flex items-center justify-between">
        <a href="../modules/supplier_list.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Suppliers
        </a>
    </div>

    <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">New Category</h2>
            <p class="text-gray-500 mt-1">Add a classification for your delivery partners.</p>
        </div>
        
        <?= $message ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">Category Name</label>
                <input type="text" name="category_name" placeholder="e.g. Perishables, Electronics, etc." 
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" required autofocus>
            </div>
            
            <div class="pt-2">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-100 hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>