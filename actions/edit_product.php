<?php
require '../config/db.php'; 
include '../includes/header.php'; 

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: product_list.php"); exit(); }

// 1. Fetch current product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { die("Product not found."); }

// 2. Fetch all suppliers for the dropdown
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC")->fetchAll();

// 3. Handle the Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE products SET sku=?, product_name=?, unit_price=?, supplier_id=? WHERE product_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['sku'], $_POST['product_name'], 
            $_POST['unit_price'], $_POST['supplier_id'], $id
        ]);
        
        header("Location: ../modules/product_list.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        $error = "Update failed: " . ($e->getCode() == 23000 ? "SKU already exists." : $e->getMessage());
    }
}
?>

<div class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Product</h2>
        <a href="../modules/product_list.php" class="text-gray-400 hover:text-gray-600 text-sm">Cancel</a>
    
    <?php if(isset($error)): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-xs font-bold uppercase tracking-tight"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">SKU (Unique ID)</label>
            <input type="text" name="sku" value="<?= htmlspecialchars($product['sku']) ?>" required 
                   class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-mono text-blue-600">
        </div>

        <div>
            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Product Name</label>
            <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required 
                   class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Unit Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" value="<?= htmlspecialchars($product['unit_price']) ?>" required 
                       class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Supplier</label>
                <select name="supplier_id" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none">
                    <option value="">No Supplier</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>" <?= $product['supplier_id'] == $s['supplier_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-100 transition transform active:scale-[0.98]">
            Update Product Details
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>