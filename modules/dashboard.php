<?php
require '../config/db.php';
include '../includes/header.php';

// --- DATA FETCHING ---
$suppliersCount = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

//  Pulling from the correct table name 'delivery_orders'
$ordersCount = $pdo->query("SELECT COUNT(*) FROM delivery_orders")->fetchColumn();

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

<div class="dashboard-container">
    <header class="dashboard-header">
        <div>
            <h1 class="page-title">System Overview</h1>
            <p class="page-subtitle">Real-time logistics & inventory analytics</p>
        </div>
        <div class="header-actions" style="display: flex; gap: 0.75rem;">
            <a href="../actions/add_supplier.php" class="btn-primary" style="background-color: #1e293b;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Supplier
            </a>
            <a href="../actions/add_product.php" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Product
            </a>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4a1 1 0 011-1h2a1 1 0 011 1v3M12 21v-3a1 1 0 011-1h2a1 1 0 011 1v3" />
                </svg>
            </div>
            <div>
                <p class="stat-label">Total Suppliers</p>
                <h2 class="stat-value"><?= $suppliersCount ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="stat-label">Total Products</p>
                <h2 class="stat-value"><?= $productsCount ?></h2>
            </div>
        </div>

        <a href="../modules/order_list.php" style="text-decoration: none; color: inherit;">
            <div class="stat-card hover:bg-gray-50 transition-all">
                <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <p class="stat-label">Total Orders</p>
                    <h2 class="stat-value"><?= $ordersCount ?></h2>
                </div>
            </div>
        </a>

        <div class="time-card">
            <p class="stat-label">Current Year</p>
            <h2 class="stat-value text-gray-400"><?= $currentYear ?></h2>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Monthly Product Growth</h3>
                <span class="badge-blue">Trend Analysis</span>
            </div>
            <div class="canvas-wrapper">
                <canvas id="productsChart"></canvas>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Inventory by Supplier</h3>
                <span class="badge-purple">Stock Distribution</span>
            </div>
            <div class="canvas-wrapper">
                <canvas id="supplierChart"></canvas>
            </div>
        </div>
    </div>

    <div class="content-bottom-grid">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Recently Added Items</h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Product Detail</th>
                            <th class="text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProducts as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <span class="name"><?= htmlspecialchars($item['product_name']) ?></span>
                                        <span class="vendor"><?= htmlspecialchars($item['supplier_name'] ?? 'General') ?></span>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span class="status-pill">New Entry</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="info-sidebar">
            <div class="secure-badge">
                <div class="badge-icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h4>Manager Access</h4>
                <p>Advanced inventory permissions are active for your account.</p>
                <a href="../modules/product_list.php" class="btn-outline">Full Inventory View</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

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
                pointHoverRadius: 6,
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
                    max: 100,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        padding: 10
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('supplierChart'), {
        type: 'bar',
        data: {
            labels: <?= $supplierNamesJSON ?>,
            datasets: [{
                data: <?= $supplierTotalsJSON ?>,
                backgroundColor: '#818cf8',
                hoverBackgroundColor: '#6366f1',
                borderRadius: 6,
                barThickness: 15
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
                        color: '#f1f5f9'
                    },
                    ticks: {
                        padding: 10
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
</script>