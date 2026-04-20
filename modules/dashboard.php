<?php
require '../config/db.php';
include '../includes/header.php';

// --- DATA FETCHING ---
$suppliersCount = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$ordersCount = $pdo->query("SELECT COUNT(*) FROM delivery_orders")->fetchColumn();
$totalRevenue = $pdo->query("
    SELECT SUM(oi.quantity * oi.unit_price_at_order) 
    FROM order_items oi
    JOIN delivery_orders o ON oi.order_id = o.order_id
    WHERE LOWER(o.status) = 'received'
")->fetchColumn() ?: 0;

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
    $monthName = date('M', mktime(0, 0, 0, $m, 1));
    $monthlyData[$m] = ['label' => "$monthName", 'total' => 0];
}
$results = $pdo->query("SELECT MONTH(created_at) as m, COUNT(*) as total FROM products WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)")->fetchAll();
foreach ($results as $row) {
    $monthlyData[(int)$row['m']]['total'] = (int)$row['total'];
}

$monthsJSON = json_encode(array_column($monthlyData, 'label'));
$monthTotalsJSON = json_encode(array_column($monthlyData, 'total'));
?>

<link rel="stylesheet" href="../assets/dashboard-styles.css">

<div class="dashboard-container mt-6 px-8">
    <header class="dashboard-header mb-8 flex justify-between items-end">
        <div>
            <h1 class="page-title text-3xl font-black text-gray-900 tracking-tight">System Overview</h1>
            <p class="page-subtitle text-gray-500 text-xs font-bold uppercase tracking-[0.2em]">Real-time logistics analytics</p>
        </div>
        <div class="header-actions flex gap-4">
            <a href="../actions/add_supplier.php" class="flex items-center gap-3 px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-sm hover:bg-slate-800 hover:-translate-y-1 active:scale-95 transition-all shadow-xl shadow-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Supplier
            </a>
            <a href="../actions/add_product.php" class="flex items-center gap-3 px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-sm hover:bg-blue-700 hover:-translate-y-1 active:scale-95 transition-all shadow-xl shadow-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Product
            </a>
            <a href="../actions/export_dashboard.php"
                target="download-frame"
                data-no-smooth-nav="true"
                class="flex items-center gap-3 px-8 py-4 bg-green-600 text-white rounded-2xl font-black text-sm hover:bg-green-700 hover:-translate-y-1 active:scale-95 transition-all shadow-xl ">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>
    </header>

    <div class="stats-grid mb-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <a href="../modules/supplier_list.php" class="group block">
        <div class="stat-card h-full p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-sm group-hover:shadow-2xl group-hover:shadow-blue-100 group-hover:-translate-y-2 transition-all duration-300 flex items-center gap-6">
            <div class="stat-icon w-14 h-14 shrink-0 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center transition-transform group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4a1 1 0 011-1h2a1 1 0 011 1v3M12 21v-3a1 1 0 011-1h2a1 1 0 011 1v3" />
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Suppliers</p>
                <h2 class="text-3xl font-black text-gray-900 tracking-tighter"><?= $suppliersCount ?></h2>
            </div>
        </div>
    </a>

    <a href="../modules/product_list.php" class="group block">
        <div class="stat-card h-full p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-sm group-hover:shadow-2xl group-hover:shadow-green-100 group-hover:-translate-y-2 transition-all duration-300 flex items-center gap-6">
            <div class="stat-icon w-14 h-14 shrink-0 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center transition-transform group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Products</p>
                <h2 class="text-3xl font-black text-gray-900 tracking-tighter"><?= $productsCount ?></h2>
            </div>
        </div>
    </a>

    <a href="../modules/order_list.php" class="group block">
        <div class="stat-card h-full p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-sm group-hover:shadow-2xl group-hover:shadow-purple-100 group-hover:-translate-y-2 transition-all duration-300 flex items-center gap-6">
            <div class="stat-icon w-14 h-14 shrink-0 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center transition-transform group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Orders</p>
                <h2 class="text-3xl font-black text-gray-900 tracking-tighter"><?= $ordersCount ?></h2>
            </div>
        </div>
    </a>

    <a href="../modules/order_list.php?status=Received" class="group block">
        <div class="stat-card h-full p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-sm group-hover:shadow-2xl group-hover:shadow-emerald-100 group-hover:-translate-y-2 transition-all duration-300 flex flex-col justify-center">
            <div class="flex items-center gap-6">
                <div class="stat-icon w-14 h-14 shrink-0 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-transform group-hover:scale-110">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-1">Revenue</p>
                    <h2 class="text-2xl font-black text-emerald-600 tracking-tighter">₱<?= number_format($totalRevenue, 2) ?></h2>
                </div>
            </div>
        </div>
    </a>
</div>

    <div class="charts-grid mb-10 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="chart-container bg-white p-8 border border-gray-100 rounded-[2.5rem]">
            <div class="chart-header flex justify-between items-center mb-6">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Monthly Growth</h3>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase">Analysis</span>
            </div>
            <div class="h-64">
                <canvas id="productsChart"></canvas>
            </div>
        </div>

        <div class="chart-container bg-white p-8 border border-gray-100 rounded-[2.5rem]">
            <div class="chart-header flex justify-between items-center mb-6">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Supplier Stock</h3>
                <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-lg text-[10px] font-black uppercase">Distribution</span>
            </div>
            <div class="h-64">
                <canvas id="supplierChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
        <div class="lg:col-span-2 bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Recent Added Data</h3>
            </div>
            <table class="w-full text-left">
                <tbody class="text-sm">
                    <?php foreach ($recentProducts as $item): ?>
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800 tracking-tight"><?= htmlspecialchars($item['product_name']) ?></span>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider"><?= htmlspecialchars($item['supplier_name'] ?? 'General') ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase">New Entry</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-10 rounded-[2.5rem] shadow-2xl text-center flex flex-col items-center justify-center">
                <div class="w-20 h-20 bg-white/10 text-white rounded-[2rem] flex items-center justify-center mb-8 backdrop-blur-md border border-white/10">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h4 class="text-2xl font-black text-white mb-3 tracking-tight">Manager Access</h4>
                <p class="text-slate-400 text-sm font-medium leading-relaxed mb-10 px-4">Advanced inventory permissions and data export tools are active.</p>
                <a href="../modules/product_list.php" class="w-full py-5 bg-white text-slate-900 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all hover:bg-slate-100 hover:scale-[1.02]">
                    Full Inventory View
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    if (window.Chart) {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#94a3b8';

        // --- MONTHLY GROWTH CHART ---
        const ctx1 = document.getElementById('productsChart').getContext('2d');
        const gradient = ctx1.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= $monthsJSON ?>,
                datasets: [{
                    data: <?= $monthTotalsJSON ?>,
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: 50,
                        grid: {
                            color: '#f8fafc'
                        },
                        ticks: {
                            stepSize: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // --- SUPPLIER STOCK CHART ---
        new Chart(document.getElementById('supplierChart'), {
            type: 'bar',
            data: {
                labels: <?= $supplierNamesJSON ?>,
                datasets: [{
                    data: <?= $supplierTotalsJSON ?>,
                    backgroundColor: '#818cf8',
                    hoverBackgroundColor: '#6366f1',
                    borderRadius: 10,
                    barThickness: 16
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: 50,
                        grid: {
                            color: '#f8fafc'
                        },
                        ticks: {
                            stepSize: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    } else {
        console.error('Chart.js failed to load. Please check your internet connection or the CDN link.');
    }
</script>

<?php include '../includes/footer.php'; ?>
