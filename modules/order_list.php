<?php
require '../config/db.php';
require '../includes/pagination.php';
include '../includes/header.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$supplier_filter = $_GET['supplier_id'] ?? '';
$status_filter   = $_GET['status'] ?? '';

// Fetch dropdown data
$suppliers = $pdo->query("SELECT supplier_id, name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$statuses  = $pdo->query("SELECT DISTINCT status FROM delivery_orders ORDER BY status ASC")->fetchAll(PDO::FETCH_COLUMN);

// BASE QUERY
$baseQuery = "FROM delivery_orders o 
              LEFT JOIN suppliers s ON o.supplier_id = s.supplier_id 
              LEFT JOIN users u ON o.created_by = u.user_id
              LEFT JOIN order_items oi ON o.order_id = oi.order_id
              WHERE 1=1";

$params = [];

// Filters
if (!empty($supplier_filter)) {
    $baseQuery .= " AND o.supplier_id = ?";
    $params[] = $supplier_filter;
}

if (!empty($status_filter)) {
    $baseQuery .= " AND o.status = ?";
    $params[] = $status_filter;
}

// COUNT QUERY 
$countQuery = "SELECT COUNT(DISTINCT o.order_id) " . $baseQuery;

$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// MAIN QUERY
$query = "SELECT 
            o.*, 
            s.name as supplier_name, 
            u.username as creator_name,
            COUNT(oi.item_id) as unique_products,
            COALESCE(SUM(oi.quantity), 0) as total_quantity,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) as total_order_value
          " . $baseQuery . "
          GROUP BY o.order_id
          ORDER BY o.order_id DESC
          LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasFilters = $supplier_filter || $status_filter;
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Delivery Orders</h2>
        <p class="text-gray-500 text-sm">Track upcoming shipments and order statuses</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="../actions/add_order.php"
            class="flex items-center justify-center px-5 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Order
        </a>
        <a href="../actions/export_orders.php?<?= http_build_query($_GET) ?>"
            target="download-frame"
            data-no-smooth-nav="true"
            class="flex items-center justify-center px-5 py-3 bg-green-600 text-white rounded-2xl font-bold text-sm hover:bg-green-700 transition-all shadow-lg shadow-green-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Excel
        </a>
    </div>
</div>

<form method="GET" action="order_list.php" class="bg-white p-4 rounded-lg shadow-sm border mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Supplier</label>
            <select name="supplier_id" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white font-medium text-gray-700">
                <option value="">All Suppliers</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['supplier_id'] ?>" <?= $supplier_filter == $s['supplier_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1 ml-1">Status</label>
            <select name="status" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white font-medium text-gray-700">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st ?>" <?= $status_filter == $st ? 'selected' : '' ?>>
                        <?= ucfirst(htmlspecialchars($st)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-gray-800 text-white py-2 rounded hover:bg-gray-700 transition text-sm font-bold">Apply Filter</button>
            <?php if ($hasFilters): ?>
                <a href="order_list.php" class="flex-1 bg-gray-100 text-gray-600 py-2 rounded hover:bg-gray-200 transition text-sm font-bold text-center border">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

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
                    <td class="p-4 text-center">
                        <div class="flex justify-center items-center gap-2">
                            <button onclick="viewOrder(<?= htmlspecialchars(json_encode($o)) ?>)"
                                class="p-2 text-green-500 bg-green-50 rounded-lg hover:bg-green-600 hover:text-white transition-all inline-block mr-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <a href="../actions/edit_order.php?id=<?= $o['order_id'] ?>"
                                class="p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <button onclick="openDeleteModal('../actions/delete_order.php?id=<?= $o['order_id'] ?>')"
                                class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" class="p-12 text-center text-gray-400 italic font-medium">No orders match your filter criteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php renderPagination($page, $totalPages, $totalRows, $limit); ?>

<div id="itemsModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transition-all transform">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-xl font-black text-gray-900">Delivery Contents</h3>
            <button onclick="closeItemsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modalBody" class="p-6 max-h-[400px] overflow-y-auto"></div>
    </div>
</div>

<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center">
        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900">Remove Order?</h3>
        <p class="text-gray-500 mt-2 text-sm">This action cannot be undone. This will permanently delete this order record.</p>
        <div class="flex gap-3 mt-8">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-3 bg-red-600 text-white text-center rounded-2xl font-bold hover:bg-red-700 transition">Delete</a>
        </div>
    </div>
</div>

<div id="viewModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-2xl font-black text-gray-900">Order Overview</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modalContent" class="space-y-4">
            </div>
            <button onclick="closeModal()" class="w-full mt-8 py-4 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition">Close Preview</button>
        </div>
    </div>
</div>

<script>
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
                <thead><tr class="text-[10px] font-bold text-gray-400 uppercase border-b"><th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Price</th></tr></thead>
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

    function openDeleteModal(url) {
        document.getElementById('confirmDeleteBtn').href = url;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function viewOrder(data) {
        const content = document.getElementById('modalContent');
        content.innerHTML = `
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="p-4 bg-blue-50 rounded-2xl">
                <p class="text-[10px] font-bold text-blue-400 uppercase">Order ID</p>
                <p class="font-mono font-bold text-blue-800 text-lg">#ORD-${data.order_id}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Status</p>
                <p class="font-bold text-gray-800 uppercase">${data.status}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl col-span-2">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Supplier</p>
                <p class="font-bold text-gray-800">${data.supplier_name || 'N/A'}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Total Items</p>
                <p class="font-bold text-gray-800">${data.total_quantity || 0} units</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Order Value</p>
                <p class="font-bold text-blue-600 text-lg">₱${parseFloat(data.total_order_value || 0).toLocaleString()}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl col-span-2">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Expected Delivery</p>
                <p class="font-bold text-gray-800">${data.expected_date}</p>
            </div>
        </div>`;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>
