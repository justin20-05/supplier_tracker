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

// 1. Products per supplier (Fixed Query for Bar Chart)
$productsPerSupplier = $pdo->query("
    SELECT s.name, COUNT(p.product_id) as total
    FROM suppliers s
    LEFT JOIN products p ON s.supplier_id = p.supplier_id
    GROUP BY s.supplier_id
")->fetchAll();

$supplierNames = array_column($productsPerSupplier, 'name');
$supplierTotals = array_column($productsPerSupplier, 'total');

// 2. Products added per day (Fixed Query for Line Chart)
$productsPerDay = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as total
    FROM products
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
")->fetchAll();

$dates = array_column($productsPerDay, 'date');
$totals = array_column($productsPerDay, 'total');

// Calculate Trend Comparison
$latest = end($totals) ?: 0;
$previous = prev($totals) ?: 0;
$percentChange = ($previous > 0) ? (($latest - $previous) / $previous) * 100 : 0;

// Pass clean JSON to Javascript
$datesJSON = json_encode($dates);
$totalsJSON = json_encode($totals);
$percentJSON = json_encode(round($percentChange, 1));
$supplierNamesJSON = json_encode($supplierNames);
$supplierTotalsJSON = json_encode($supplierTotals);
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-gray-900 tracking-tight">System Overview</h1>
    <p class="text-gray-500">Logistics and Inventory Status</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="text-blue-500 font-bold text-[10px] uppercase tracking-widest mb-2">Total Suppliers</div>
        <div class="text-4xl font-black text-gray-800"><?= $suppliersCount ?></div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="text-green-500 font-bold text-[10px] uppercase tracking-widest mb-2">Total Products</div>
        <div class="text-4xl font-black text-gray-800"><?= $productsCount ?></div>
    </div>

    <a href="../actions/add_product.php" class="bg-blue-600 p-6 rounded-2xl text-white shadow-lg shadow-blue-100 hover:bg-blue-700 transition transform hover:-translate-y-1">
        <div class="text-blue-200 text-[10px] font-bold uppercase tracking-widest mb-2">Shortcuts</div>
        <div class="font-bold text-xl">Add Product →</div>
    </a>

    <a href="../actions/add_supplier.php" class="bg-gray-800 p-6 rounded-2xl text-white shadow-lg shadow-gray-200 hover:bg-gray-900 transition transform hover:-translate-y-1">
        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-2">Shortcuts</div>
        <div class="font-bold text-xl">New Supplier →</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Inventory Distribution</h3>
        <canvas id="supplierChart" height="200"></canvas>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Registration Growth</h3>
            <span id="trendIndicator" class="px-2 py-1 rounded-lg text-[10px] font-black italic uppercase"></span>
        </div>
        <canvas id="productsChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-50 bg-gray-50/50">
            <h3 class="font-bold text-gray-800 uppercase text-[10px] tracking-widest">Recently Added Items</h3>
        </div>
        <table class="w-full text-left">
            <tbody class="divide-y divide-gray-50">
                <?php foreach($recentProducts as $item): ?>
                <tr class="hover:bg-blue-50/50 transition">
                    <td class="p-4">
                        <span class="block font-bold text-gray-800 text-sm"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase"><?= htmlspecialchars($item['supplier_name'] ?? 'General') ?></span>
                    </td>
                    <td class="p-4 text-right">
                        <span class="text-[10px] font-black bg-blue-100 text-blue-700 px-2 py-1 rounded-md uppercase">New</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center flex flex-col justify-center">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="font-black text-xl text-gray-900 mb-2">Secure Access</h3>
        <p class="text-gray-500 text-sm mb-6 leading-relaxed">Manager privileges active. You can full inventory records.</p>
        <a href="../modules/product_list.php" class="inline-block bg-gray-50 text-blue-600 py-3 rounded-xl font-bold hover:bg-blue-50 transition-all border border-blue-100">
            View Full Inventory
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Data from PHP
const dates = <?= $datesJSON ?>;
const totals = <?= $totalsJSON ?>;
const percentChange = <?= $percentJSON ?>;
const sNames = <?= $supplierNamesJSON ?>;
const sTotals = <?= $supplierTotalsJSON ?>;

// 1. PRODUCTS GROWTH CHART (Line)
const trendColor = percentChange >= 0 ? '#3b82f6' : '#ef4444';
const trendIndicator = document.getElementById('trendIndicator');
trendIndicator.innerHTML = (percentChange >= 0 ? '▲ +' : '▼ ') + percentChange + '% Growth';
trendIndicator.classList.add(percentChange >= 0 ? 'text-blue-600' : 'text-red-600');
trendIndicator.classList.add(percentChange >= 0 ? 'bg-blue-50' : 'bg-red-50');

new Chart(document.getElementById('productsChart'), {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            label: 'New Products',
            data: totals,
            borderColor: trendColor,
            backgroundColor: 'transparent',
            pointBackgroundColor: '#fff',
            pointBorderWidth: 3,
            tension: 0.4,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { display: false }, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});

// 2. SUPPLIER DISTRIBUTION CHART (Bar)
new Chart(document.getElementById('supplierChart'), {
    type: 'bar',
    data: {
        labels: sNames,
        datasets: [{
            data: sTotals,
            backgroundColor: '#818cf8',
            borderRadius: 8,
            barThickness: 20
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>