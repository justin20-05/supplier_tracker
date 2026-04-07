<?php
require '../config/db.php';
include '../includes/header.php';

// Fetch suppliers for the dropdown
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $_POST['sku'];
    $name = $_POST['product_name'];
    $price = $_POST['unit_price'];
    $s_id = $_POST['supplier_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO products (sku, product_name, unit_price, supplier_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sku, $name, $price, $s_id]);
        header("Location: ../modules/product_list.php?msg=added");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . ($e->getCode() == 23000 ? "SKU must be unique." : $e->getMessage());
    }
}
?>

<div class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Add New Product</h2>
    
    <?php if(isset($error)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Stock Keeping Unit (SKU)</label>
            <input type="text" name="sku" placeholder="e.g. ELEC-LAP-001" required 
                   class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Product Name</label>
            <input type="text" name="product_name" required 
                   class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Unit Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" required 
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Assign Supplier</label>
                <select name="supplier_id" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    <option value="">Select Vendor</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <a href="../modules/product_list.php" class="flex-1 text-center py-3 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition">Cancel</a>
            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition">Save Product</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>