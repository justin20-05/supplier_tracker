<?php
require '../config/db.php';
include '../includes/header.php';

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
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-50 last:border-0">
                    <td class="p-4 font-mono font-bold text-blue-600">#ORD-<?= $o['order_id'] ?></td>
                    <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($o['supplier_name'] ?? 'N/A') ?></td>
                    <td class="p-4 text-gray-500"><?= date('M d, Y', strtotime($o['expected_date'])) ?></td>
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
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="5" class="p-12 text-center text-gray-400 italic font-medium">No delivery orders recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
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
            <p class="text-gray-500 mt-2 text-sm leading-relaxed">This will permanently delete the delivery order. This action cannot be undone.</p>
        </div>
        <div class="flex gap-3 mt-8">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 bg-red-600 text-white text-center rounded-2xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">Delete</a>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(url) {
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>