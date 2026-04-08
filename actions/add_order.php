<?php
require '../config/db.php';
include '../includes/header.php';

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $expected_date = $_POST['expected_date'];
    $status = $_POST['status'];
    $created_by = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO delivery_orders (supplier_id, expected_date, status, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $expected_date, $status, $created_by]);
        header("Location: ../modules/order_list.php?msg=added");
        exit();
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-2xl mb-6'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-3xl font-black text-gray-900 mb-6">Create Delivery Order</h2>
        <?= $message ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Supplier</label>
                <select name="supplier_id" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="" disabled selected>Select a Supplier</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Expected Date</label>
                    <input type="date" name="expected_date" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                    <select name="status" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option value="Pending">Pending</option>
                        <option value="Received">Received</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition">Save Order</button>
                <a href="../modules/order_list.php" class="flex-1 text-center py-4 text-gray-500 font-bold hover:text-gray-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>