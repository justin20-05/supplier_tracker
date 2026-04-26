<?php
require '../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../modules/supplier_list.php");
    exit();
}

// Update request logic must be BEFORE header.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, category=? 
                WHERE supplier_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['contact_person'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['category'],
            $id
        ]);

        // Redirect back to list after successful update
        header("Location: ../modules/supplier_list.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        $error_msg = "Update failed: " . $e->getMessage();
    }
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    die("Supplier not found.");
}

$all_cats = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mt-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Edit Supplier</h2>
            <p class="text-xs text-gray-400 font-medium">Update profile for <?= htmlspecialchars($supplier['name']) ?></p>
        </div>
        <a href="../modules/supplier_list.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition">Cancel</a>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 font-bold text-sm"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Supplier Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($supplier['name']) ?>" required
                class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Contact Person</label>
                <input type="text" name="contact_person" value="<?= htmlspecialchars($supplier['contact_person']) ?>" required
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Category</label>
                <select name="category" required 
                        class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <?php foreach ($all_cats as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category_name']) ?>"
                            <?= ($supplier['category'] == $cat['category_name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($supplier['email']) ?>" required
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($supplier['phone']) ?>"
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-100 hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all active:scale-[0.98]">
                Update Information
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>