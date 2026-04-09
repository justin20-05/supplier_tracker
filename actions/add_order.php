<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = (int)$_POST['supplier_id'];
    $expected_date = $_POST['expected_date'];
    $status = $_POST['status'];
    $created_by = (int)$_SESSION['user_id'];

    $product_ids = $_POST['products'] ?? [];
    $quantities = $_POST['quantities'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Insert the main order
        $stmt = $pdo->prepare("INSERT INTO delivery_orders (supplier_id, expected_date, status, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $expected_date, $status, $created_by]);
        $order_id = $pdo->lastInsertId();

        // 2. Prepare statements for items
        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price_at_order) VALUES (?, ?, ?, ?)");
        $priceStmt = $pdo->prepare("SELECT unit_price FROM products WHERE product_id = ?");

        foreach ($product_ids as $index => $p_id) {
            if (empty($p_id)) continue;

            // Fetch current price
            $priceStmt->execute([$p_id]);
            $current_price = $priceStmt->fetchColumn();

            $qty = (int)$quantities[$index];
            $unit_p = (float)$current_price;

            $itemStmt->execute([$order_id, $p_id, $qty, $unit_p]);
        }

        $pdo->commit();
        header("Location: ../modules/order_list.php?msg=added");
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-2xl mb-6 font-bold text-sm'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto mt-10 mb-20 px-4">
    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-3xl font-black text-gray-900 mb-2 tracking-tight">Create Delivery Order</h2>
        <p class="text-gray-400 text-sm font-medium uppercase tracking-widest mb-8">Manage new supplier shipment</p>

        <?= $message ?>

        <form method="POST" id="orderForm" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Supplier</label>
                <select name="supplier_id" id="supplier_id" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <option value="" disabled selected>Select a Supplier</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Expected Date</label>
                    <input type="date" name="expected_date" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                    <select name="status" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                        <option value="Pending">Pending</option>
                        <option value="Received">Received</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Order Items</h3>
                    <button type="button" id="add-item-btn" class="text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-wider disabled:opacity-30" disabled>+ Add Product</button>
                </div>

                <div id="items-container" class="space-y-3">
                    <p id="placeholder-text" class="text-gray-400 text-sm italic text-center py-6 bg-gray-50 rounded-2xl border border-dashed border-gray-200">Please select a supplier to start adding products.</p>
                </div>
            </div>

            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition transform active:scale-95">Save Order</button>
                <a href="../modules/order_list.php" class="flex-1 text-center py-4 text-gray-400 font-bold hover:text-gray-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const supplierSelect = document.getElementById('supplier_id');
        const itemsContainer = document.getElementById('items-container');
        const addItemBtn = document.getElementById('add-item-btn');
        let availableProducts = [];

        supplierSelect.addEventListener('change', async function() {
            const supplierId = this.value;
            const response = await fetch(`get_products.php?supplier_id=${supplierId}`);
            availableProducts = await response.json();

            itemsContainer.innerHTML = '';
            addItemBtn.disabled = false;

            if (availableProducts.length > 0) {
                addItemRow();
            } else {
                itemsContainer.innerHTML = '<p class="text-red-400 text-sm text-center py-4">This supplier has no products registered.</p>';
            }
        });

        addItemBtn.addEventListener('click', addItemRow);

        function addItemRow() {
            const row = document.createElement('div');
            row.className = "flex gap-3 items-center bg-gray-50 p-3 rounded-2xl border border-gray-100 animate-in fade-in slide-in-from-top-2";

            let options = availableProducts.map(p => `<option value="${p.product_id}">${p.product_name} (₱${parseFloat(p.unit_price).toLocaleString()})</option>`).join('');

            row.innerHTML = `
            <div class="flex-1">
                <select name="products[]" required class="w-full p-3 bg-white border border-gray-200 rounded-xl outline-none text-sm font-semibold">
                    <option value="">Select Product</option>
                    ${options}
                </select>
            </div>
            <div class="w-24">
                <input type="number" name="quantities[]" min="1" value="1" required class="w-full p-3 bg-white border border-gray-200 rounded-xl outline-none text-sm font-bold text-blue-600 text-center" placeholder="Qty">
            </div>
            <button type="button" class="remove-btn text-red-400 hover:text-red-600 p-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        `;

            itemsContainer.appendChild(row);
            row.querySelector('.remove-btn').addEventListener('click', () => {
                row.remove();
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>