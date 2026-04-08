<?php
require '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM delivery_orders WHERE order_id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) { header("Location: ../modules/order_list.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("UPDATE delivery_orders SET expected_date = ?, status = ? WHERE order_id = ?");
    $stmt->execute([$_POST['expected_date'], $_POST['status'], $id]);
    header("Location: ../modules/order_list.php?msg=updated");
    exit();
}
?>

<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-3xl font-black text-gray-900 mb-2">Edit Order #<?= $id ?></h2>
        <p class="text-gray-400 text-sm mb-8 font-medium italic uppercase tracking-widest">Update delivery tracking</p>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Expected Delivery Date</label>
                <input type="date" name="expected_date" value="<?= $order['expected_date'] ?>" required 
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Current Status</label>
                <select name="status" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Received" <?= $order['status'] == 'Received' ? 'selected' : '' ?>>Received</option>
                    <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition">Update Order</button>
                <a href="../modules/order_list.php" class="flex-1 text-center py-4 text-gray-500 font-bold hover:text-gray-800 transition">Back</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>