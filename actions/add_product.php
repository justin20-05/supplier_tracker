<?php
require '../config/db.php';

//  PERFORM ALL LOGIC AND REDIRECTS BEFORE ANY OUTPUT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $product_code = $_POST['product_code']; 
    $supplier_id  = $_POST['supplier_id'];
    $unit_price   = $_POST['unit_price'];
    $stock        = $_POST['stock'];

    try {
        $sql = "INSERT INTO products (product_name, product_code, supplier_id, unit_price, stock) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_name, $product_code, $supplier_id, $unit_price, $_POST['stock']]);

        header("Location: ../modules/product_list.php?msg=added");
        exit();
        
    } catch (PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

include '../includes/header.php';

$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC")->fetchAll();
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mt-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Add New Product</h2>
            <p class="text-xs text-gray-400 font-medium">Register a new item to your inventory</p>
        </div>
        <a href="../modules/product_list.php" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">← Back to List</a>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class='bg-red-100 text-red-700 p-3 rounded mb-4 font-bold'><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Product Name</label>
            <input type="text" name="product_name" placeholder="Enter product name" required 
                   class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Product Code</label>
                <input type="text" name="product_code" placeholder="SKU-001" required
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Supplier</label>
                <select name="supplier_id" required 
                        class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <option value="" disabled selected>Select Supplier</option>
                    <?php foreach($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>">
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Product's Stock</label>
            <input type="number" name="stock" value="0" min="0" required 
           class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-700">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Unit Price (₱)</label>
            <input type="number" step="0.01" name="unit_price" placeholder="0.00" required 
                   class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-100 hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all active:scale-[0.98]">
                Save Product
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>