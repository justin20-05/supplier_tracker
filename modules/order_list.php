<?php
require '../config/db.php';
include '../includes/header.php';

// Fetch orders with Supplier names and creator info
$query = "SELECT o.*, s.name as supplier_name, u.username as creator_name 
          FROM delivery_orders o 
          LEFT JOIN suppliers s ON o.supplier_id = s.supplier_id 
          LEFT JOIN users u ON o.created_by = u.user_id
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
                <th class="p-4 border-b">Status</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($orders as $o): ?>
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-50">
                    <td class="p-4 font-mono font-bold text-blue-600">#ORD-<?= $o['order_id'] ?></td>
                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($o['supplier_name'] ?? 'N/A') ?></td>
                    <td class="p-4 text-gray-500"><?= date('M d, Y', strtotime($o['expected_date'])) ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase 
                        <?= $o['status'] == 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                            <?= $o['status'] ?>
                        </span>
                    </td>
                    <td class="p-4 text-center space-x-3">
                        <a href="../actions/edit_order.php?id=<?= $o['order_id'] ?>"
                            class="text-blue-600 font-bold text-[10px] uppercase tracking-wider">Edit</a>

                        <button onclick="openDeleteModal('../actions/delete_order.php?id=<?= $o['order_id'] ?>')"
                            class="text-red-400 font-bold text-[10px] uppercase tracking-wider">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="5" class="p-12 text-center text-gray-400 italic">No delivery orders recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>