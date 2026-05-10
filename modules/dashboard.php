<?php
require '../config/db.php';
include '../includes/header.php';

//  CTE 
$statsQuery = $pdo->query("
    WITH DashboardStats AS (
        SELECT 
            (SELECT COUNT(*) FROM suppliers) as total_suppliers,
            (SELECT COUNT(*) FROM products) as total_products,
            (SELECT COUNT(*) FROM delivery_orders) as total_orders,
            (SELECT SUM(oi.quantity * oi.unit_price_at_order) 
             FROM order_items oi
             JOIN delivery_orders o ON oi.order_id = o.order_id
             WHERE LOWER(o.status) = 'received') as total_revenue
    )
    SELECT * FROM DashboardStats
");
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

// variables from CTE 
$suppliersCount = $stats['total_suppliers'];
$productsCount = $stats['total_products'];
$ordersCount = $stats['total_orders'];
$totalRevenue = $stats['total_revenue'] ?: 0;

$recentProducts = $pdo->query("SELECT p.product_name, s.name as supplier_name 
                               FROM products p 
                               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id 
                               ORDER BY p.product_id DESC LIMIT 5")->fetchAll();

$productsPerSupplier = $pdo->query("SELECT s.name, COUNT(p.product_id) as total FROM suppliers s LEFT JOIN products p ON s.supplier_id = p.supplier_id GROUP BY s.supplier_id")->fetchAll();
$supplierNamesJSON = json_encode(array_column($productsPerSupplier, 'name'));
$supplierTotalsJSON = json_encode(array_column($productsPerSupplier, 'total'));

$currentYear = date('Y');
$monthlyData = [];
for ($m = 1; $m <= 12; $m++) {
    $monthlyData[] = $pdo->query("SELECT COUNT(*) FROM delivery_orders WHERE MONTH(created_at) = $m AND YEAR(created_at) = $currentYear")->fetchColumn();
}
$monthlyDataJSON = json_encode($monthlyData);
?>

<div class="p-6 lg:p-10 bg-gray-50 min-h-screen">
    <div class="mb-10 flex justify-between items-end">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase">Dashboard</h1>
            <p class="text-gray-500 font-medium">Welcome back, <span class="text-blue-600 font-bold"><?= htmlspecialchars($_SESSION['username']) ?></span></p>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Current Date</p>
            <p class="font-bold text-slate-900"><?= date('F d, Y') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-blue-50 rounded-2xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-full">Php</span>
            </div>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Total Revenue</p>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter">₱<?= number_format($totalRevenue, 2) ?></h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all">
            <div class="p-3 bg-indigo-50 w-fit rounded-2xl text-indigo-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Active Suppliers</p>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $suppliersCount ?></h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all">
            <div class="p-3 bg-emerald-50 w-fit rounded-2xl text-emerald-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 11m8 4L4 19M4 11v10l8 4m0-10l8 4m-8-4V3"/></svg>
            </div>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Total Products</p>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $productsCount ?></h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all">
            <div class="p-3 bg-amber-50 w-fit rounded-2xl text-amber-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
            </div>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Total Orders</p>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $ordersCount ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-10 rounded-[3rem] border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Product Distribution</h3>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Items per Supplier</p>
                </div>
                <div class="flex gap-2">
                    <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                    <div class="w-3 h-3 bg-gray-100 rounded-full"></div>
                </div>
            </div>
            <div class="h-[350px]">
                <canvas id="supplierChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[3rem] border border-gray-100 shadow-sm">
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter mb-8">Recently Added</h3>
            <div class="space-y-6">
                <?php foreach ($recentProducts as $product): ?>
                <div class="flex items-center gap-4 group cursor-pointer">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-blue-600 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <?= strtoupper(substr($product['product_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-black text-gray-800 text-sm tracking-tight group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($product['product_name']) ?></p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($product['supplier_name']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <a href="product_list.php" class="mt-10 block w-full py-4 bg-gray-50 rounded-2xl text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:bg-blue-600 hover:text-white transition-all">
                View All Products
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('supplierChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $supplierNamesJSON ?>,
                datasets: [{
                    label: 'Products',
                    data: <?= $supplierTotalsJSON ?>,
                    backgroundColor: '#2563eb',
                    hoverBackgroundColor: '#1e40af',
                    borderRadius: 12,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f8fafc', drawBorder: false },
                        ticks: { font: { weight: 'bold' }, color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold' }, color: '#94a3b8' }
                    }
                }
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>