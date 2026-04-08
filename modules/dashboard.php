<?php
require '../config/db.php';   
include '../includes/header.php'; 

// Fetch counts for the Dashboard cards
$suppliersCount = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Fetch 5 most recently added products
$recentProducts = $pdo->query("SELECT p.product_name, s.name as supplier_name 
                               FROM products p 
                               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
                               ORDER BY p.product_id DESC LIMIT 5")->fetchAll();

// Products per supplier
$productsPerSupplier = $pdo->query("
    SELECT s.name, COUNT(p.product_id) as total
    FROM suppliers s
    LEFT JOIN products p ON s.supplier_id = p.supplier_id
    GROUP BY s.supplier_id
")->fetchAll();

// Products added per day (last 7 days)
$productsPerDay = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as total
    FROM products
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
")->fetchAll();

// Convert to JSON for charts
$supplierNames = json_encode(array_column($productsPerSupplier, 'name'));
$supplierTotals = json_encode(array_column($productsPerSupplier, 'total'));

$dates = json_encode(array_column($productsPerDay, 'date'));
$totals = json_encode(array_column($productsPerDay, 'total'));
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-gray-900 tracking-tight">System Overview</h1>
    <p class="text-gray-500">Logistics and Inventory Status</p>
</div>

<!-- DASHBOARD CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <div class="text-blue-500 font-bold text-xs uppercase mb-2">Total Suppliers</div>
        <div class="text-4xl font-black text-gray-800"><?= $suppliersCount ?></div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <div class="text-green-500 font-bold text-xs uppercase mb-2">Total Products</div>
        <div class="text-4xl font-black text-gray-800"><?= $productsCount ?></div>
    </div>

    <a href="../actions/add_product.php" class="bg-blue-600 p-6 rounded-2xl text-white shadow-lg hover:bg-blue-700 transition">
        <div class="text-blue-200 text-xs mb-2">Shortcuts</div>
        <div class="font-bold text-xl">Add Product →</div>
    </a>

    <a href="../actions/add_supplier.php" class="bg-gray-800 p-6 rounded-2xl text-white shadow-lg hover:bg-gray-900 transition">
        <div class="text-gray-400 text-xs mb-2">Shortcuts</div>
        <div class="font-bold text-xl">New Supplier →</div>
    </a>
</div>

<!-- ANALYTICS -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <h3 class="font-bold text-gray-700 mb-4">Products per Supplier</h3>
        <canvas id="supplierChart"></canvas>
    </div>

    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <h3 class="font-bold text-gray-700 mb-4">Products Added (Last 7 Days)</h3>
        <canvas id="productsChart"></canvas>
    </div>

</div>

<!-- MAIN CONTENT -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2 bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="font-bold text-gray-800 uppercase text-xs">Recently Added Items</h3>
        </div>
        <table class="w-full text-left">
            <tbody>
                <?php foreach($recentProducts as $item): ?>
                <tr class="hover:bg-blue-50 transition">
                    <td class="p-4">
                        <span class="block font-bold"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="text-xs text-gray-400"><?= htmlspecialchars($item['supplier_name'] ?? 'General') ?></span>
                    </td>
                    <td class="p-4 text-right">
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">NEW</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white p-8 rounded-2xl border shadow-sm text-center">
        <h3 class="font-black text-lg mb-2">Secure Access</h3>
        <p class="text-gray-500 text-sm mb-4">
            You are logged in as an authorized manager.
        </p>
        <a href="../modules/product_list.php" class="text-blue-600 font-bold hover:underline">
            Review Full Inventory
        </a>
    </div>

</div>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const supplierNames = <?= $supplierNames ?>;
const supplierTotals = <?= $supplierTotals ?>;

const dates = <?= $dates ?>;
const totals = <?= $totals ?>;

// Bar Chart
new Chart(document.getElementById('supplierChart'), {
    type: 'bar',
    data: {
        labels: supplierNames,
        datasets: [{
            label: 'Products',
            data: supplierTotals,
            backgroundColor: '#3B82F6'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Line Chart
new Chart(document.getElementById('productsChart'), {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            label: 'Products Added',
            data: totals,
            borderColor: '#10B981',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true
    }
});
</script>

<?php include '../includes/footer.php'; ?>