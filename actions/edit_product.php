<?php
require '../config/db.php'; 

// 1. HANDLE REDIRECTS AND POST LOGIC AT THE TOP
$id = $_GET['id'] ?? null;
if (!$id) { 
    header("Location: ../modules/product_list.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE products SET product_code=?, product_name=?, unit_price=?, supplier_id=? WHERE product_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['product_code'], 
            $_POST['product_name'], 
            $_POST['unit_price'], 
            $_POST['supplier_id'], 
            $id
        ]);
        
        header("Location: ../modules/product_list.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        $error = "Update failed: " . ($e->getCode() == 23000 ? "Product code already exists." : $e->getMessage());
    }
}

// 2. FETCH DATA FOR THE FORM
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { die("Product not found."); }

// FIXED: Changed '.' to '->' and ordered by product_id DESC for newest first
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY supplier_id DESC")->fetchAll();

// 3. NOW INCLUDE THE HEADER
include '../includes/header.php'; 
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mt-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Edit Product</h2>
            <p class="text-xs text-gray-400 font-medium">Update details for SKU: <?= htmlspecialchars($product['product_code']) ?></p>
        </div>
        <a href="../modules/product_list.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition">Cancel</a>
    </div>

    <?php if(isset($error)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-6 font-bold text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Product Code</label>
            <input type="text" name="product_code" value="<?= htmlspecialchars($product['product_code']) ?>" required 
                   class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-mono text-blue-600 transition-all">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Product Name</label>
            <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required 
                   class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold transition-all">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Unit Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" value="<?= htmlspecialchars($product['unit_price']) ?>" required 
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-bold transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Supplier</label>
                <div class="relative">
                    <select name="supplier_id" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer transition-all">
                        <option value="">No Supplier Assigned</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['supplier_id'] ?>" <?= $product['supplier_id'] == $s['supplier_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-100 hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all active:scale-[0.98]">
                Update Product Details
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>