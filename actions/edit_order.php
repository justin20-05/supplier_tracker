<?php
require '../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../modules/order_list.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM delivery_orders WHERE order_id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: ../modules/order_list.php");
    exit();
}

$items_stmt = $pdo->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$items_stmt->execute([$id]);
$order_items = $items_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = trim($_POST['status']);
    $old_status = $order['status'];
    $expected_date = $_POST['expected_date'];
    $quantities = $_POST['quantities'] ?? []; 

    try {
        $pdo->beginTransaction();

        // 1. STOCK REFUND LOGIC
        // If the order is being changed TO cancelled FROM something else, give stock back
        if (strtolower($new_status) === 'cancelled' && strtolower($old_status) !== 'cancelled') {
            foreach ($order_items as $item) {
                $refund = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                // We use the quantity from the original order items
                $refund->execute([$item['quantity'], $item['product_id']]);
            }
        }
        // OPTIONAL: If changing FROM cancelled BACK TO active, deduct stock again
        elseif (strtolower($new_status) !== 'cancelled' && strtolower($old_status) === 'cancelled') {
            foreach ($order_items as $item) {
                $deduct = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $deduct->execute([$item['quantity'], $item['product_id']]);
            }
        }

        // 2. Update basic order info
        $update_order = $pdo->prepare("UPDATE delivery_orders SET expected_date = ?, status = ? WHERE order_id = ?");
        $update_order->execute([$expected_date, $new_status, $id]);

        // 3. Update quantities for each item
        $update_item = $pdo->prepare("UPDATE order_items SET quantity = ? WHERE item_id = ? AND order_id = ?");
        foreach ($quantities as $item_id => $qty) {
            $update_item->execute([$qty, $item_id, $id]);
        }

        $pdo->commit();
        
        header("Location: ../modules/order_list.php?msg=updated");
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Update failed: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto mt-10 px-4">
    <div class="mb-6">
        <a href="../modules/order_list.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Orders
        </a>
    </div>

    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-sm border border-gray-100">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Order #<?= htmlspecialchars($id) ?></h2>
            <p class="text-gray-400 text-sm font-medium uppercase tracking-widest">Update Delivery & Quantities</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 font-bold text-sm border border-red-100"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Expected Date</label>
                    <input type="date" name="expected_date" value="<?= $order['expected_date'] ?>" required
                        class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Current Status</label>
                    <select name="status" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="Pending" <?= ($order['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Received" <?= ($order['status'] == 'Received') ? 'selected' : '' ?>>Received</option>
                        <option value="Cancelled" <?= ($order['status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 ml-1">Order Items & Quantities</label>
                <div class="space-y-3">
                    <?php foreach ($order_items as $item): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                            <div>
                                <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></p>
                                <p class="text-[10px] text-gray-400 uppercase font-black">Price at Order: ₱<?= number_format($item['unit_price_at_order'], 2) ?></p>
                            </div>
                            <div class="w-32">
                                <input type="number" name="quantities[<?= $item['item_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" required
                                    class="w-full p-2 bg-white border border-gray-200 rounded-xl text-center font-bold text-blue-600 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition transform hover:-translate-y-0.5 active:scale-95">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>