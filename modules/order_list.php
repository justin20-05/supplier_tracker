<?php
require '../config/db.php';
include '../includes/header.php';

// query to pull item counts and total order valuation
$query = "SELECT o.*, s.name as supplier_name, u.username as creator_name,
          COUNT(oi.item_id) as unique_products,
          SUM(oi.quantity) as total_quantity,
          SUM(oi.quantity * oi.unit_price_at_order) as total_order_value
          FROM delivery_orders o 
          LEFT JOIN suppliers s ON o.supplier_id = s.supplier_id 
          LEFT JOIN users u ON o.created_by = u.user_id
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          GROUP BY o.order_id
          ORDER BY o.expected_date ASC";
$orders = $pdo->query($query)->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Delivery Orders</h2>
        <p class="text-gray-500 text-sm">Track upcoming shipments and order statuses</p>
    </div>
    <a href="../actions/add_order.php"
        class="flex items-center justify-center px-5 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
        + New Order
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-widest">
                <th class="p-4 border-b">Order ID</th>
                <th class="p-4 border-b">Supplier</th>
                <th class="p-4 border-b">Expected Date</th>
                <th class="p-4 border-b">Details</th>
                <th class="p-4 border-b">Total Value</th>
                <th class="p-4 border-b">Status</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($orders as $o): ?>
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-50 last:border-0">
                    <td class="p-4 font-mono font-bold text-blue-600">#ORD-<?= $o['order_id'] ?></td>
                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($o['supplier_name'] ?? 'N/A') ?></td>
                    <td class="p-4 text-gray-500"><?= date('M d, Y', strtotime($o['expected_date'])) ?></td>
                    
                    <td class="p-4">
                        <div class="text-sm font-bold text-gray-900"><?= $o['total_quantity'] ?? 0 ?> units</div>
                        <button onclick="viewItems(<?= $o['order_id'] ?>)" class="text-[10px] text-blue-600 font-bold uppercase hover:underline">
                            View <?= $o['unique_products'] ?> items
                        </button>
                    </td>

                    <td class="p-4 text-sm font-black text-gray-900">
                        ₱<?= number_format($o['total_order_value'] ?? 0, 2) ?>
                    </td>

                    <td class="p-4">
                        <?php
                            $status = strtolower($o['status']); 
                            $colorClasses = "bg-gray-100 text-gray-700"; 
                            if ($status == 'pending') {
                                $colorClasses = "bg-yellow-100 text-yellow-700";
                            } elseif ($status == 'received') {
                                $colorClasses = "bg-green-100 text-green-700";
                            } elseif ($status == 'cancelled') {
                                $colorClasses = "bg-red-100 text-red-700";
                            }
                        ?>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $colorClasses ?>">
                            <?= htmlspecialchars($o['status']) ?>
                        </span>
                    </td>
                    <td class="p-4 text-center space-x-3">
                        <a href="../actions/edit_order.php?id=<?= $o['order_id'] ?>"
                            class="text-blue-600 font-bold text-[10px] uppercase tracking-wider hover:underline">Edit</a>
                        <button onclick="openDeleteModal('../actions/delete_order.php?id=<?= $o['order_id'] ?>')"
                            class="text-red-400 font-bold text-[10px] uppercase tracking-wider hover:text-red-600 transition-colors">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="itemsModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transition-all transform">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-xl font-black text-gray-900">Delivery Contents</h3>
            <button onclick="closeItemsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="modalBody" class="p-6 max-h-[400px] overflow-y-auto">
            </div>
    </div>
</div>

<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center">
    <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full mx-4 transform transition-all">
        <div class="text-center">
            <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-xl font-black text-gray-900">Remove Order?</h3>
            <p class="text-gray-500 mt-2 text-sm leading-relaxed">Permanently delete this order. This action cannot be undone.</p>
        </div>
        <div class="flex gap-3 mt-8">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 bg-red-600 text-white text-center rounded-2xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">Delete</a>
        </div>
    </div>
</div>

<script>
    // JS for the Order Items Modal
    async function viewItems(orderId) {
        const modal = document.getElementById('itemsModal');
        const body = document.getElementById('modalBody');
        modal.classList.remove('hidden');
        body.innerHTML = '<p class="text-center text-gray-400 py-10">Loading items...</p>';

        try {
            const response = await fetch(`../actions/get_order_items.php?order_id=${orderId}`);
            const items = await response.json();

            if (items.length === 0) {
                body.innerHTML = '<p class="text-center text-gray-400 py-10">No items found.</p>';
                return;
            }

            let html = `<table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[10px] font-bold text-gray-400 uppercase border-b"><th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Price</th></tr>
                </thead>
                <tbody>`;
            items.forEach(item => {
                html += `<tr class="border-b border-gray-50">
                    <td class="py-3 font-bold text-gray-800">${item.product_name}</td>
                    <td class="py-3 text-gray-600">${item.quantity}</td>
                    <td class="py-3 text-right font-mono text-gray-900">₱${parseFloat(item.unit_price_at_order).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            body.innerHTML = html;
        } catch (e) {
            body.innerHTML = '<p class="text-center text-red-500 py-10">Error loading items.</p>';
        }
    }

    function closeItemsModal() {
        document.getElementById('itemsModal').classList.add('hidden');
    }

    // JS for Delete Modal
    function openDeleteModal(url) {
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>