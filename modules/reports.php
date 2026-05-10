<?php
require '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

function isValidReportDate($value)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

function formatReportDate($value)
{
    return !empty($value) ? date('M d, Y', strtotime($value)) : 'N/A';
}

$defaultFromDate = date('Y-m-01');
$defaultToDate = date('Y-m-d');

$fromDate = isValidReportDate($_GET['from_date'] ?? '') ? $_GET['from_date'] : $defaultFromDate;
$toDate = isValidReportDate($_GET['to_date'] ?? '') ? $_GET['to_date'] : $defaultToDate;

if (strtotime($fromDate) > strtotime($toDate)) {
    [$fromDate, $toDate] = [$toDate, $fromDate];
}

$dateParams = [
    ':from_date' => $fromDate,
    ':to_date' => $toDate,
];

$statsStmt = $pdo->prepare("
    WITH report_scope AS (
        SELECT
            o.order_id,
            o.status
        FROM delivery_orders o
        WHERE o.expected_date BETWEEN :from_date AND :to_date
          AND LOWER(o.status) IN ('pending', 'received', 'cancelled')
    ),
    order_rollups AS (
        SELECT
            rs.order_id,
            rs.status,
            COALESCE(SUM(oi.quantity), 0) AS item_count,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) AS order_value
        FROM report_scope rs
        LEFT JOIN order_items oi ON rs.order_id = oi.order_id
        GROUP BY rs.order_id, rs.status
    ),
    report_totals AS (
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(CASE WHEN LOWER(status) = 'received' THEN 1 ELSE 0 END), 0) AS items_received,
            COALESCE(SUM(CASE WHEN LOWER(status) = 'received' THEN order_value ELSE 0 END), 0) AS total_expenditure
        FROM order_rollups
    )
    SELECT * FROM report_totals
");
$statsStmt->execute($dateParams);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$totalOrders = (int)($stats['total_orders'] ?? 0);
$itemsReceived = (int)($stats['items_received'] ?? 0);
$totalExpenditure = (float)($stats['total_expenditure'] ?? 0);

$performanceStmt = $pdo->prepare("
    WITH supplier_rollups AS (
        SELECT
            s.supplier_id,
            s.name AS supplier_name,
            o.order_id,
            o.expected_date,
            COALESCE(SUM(oi.quantity * oi.unit_price_at_order), 0) AS order_value
        FROM delivery_orders o
        INNER JOIN suppliers s ON o.supplier_id = s.supplier_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.expected_date BETWEEN :from_date AND :to_date
          AND LOWER(o.status) = 'received'
        GROUP BY s.supplier_id, s.name, o.order_id, o.expected_date
    )
    SELECT
        supplier_name,
        COUNT(order_id) AS order_count,
        COALESCE(SUM(order_value), 0) AS total_spent,
        MAX(expected_date) AS last_transaction_date
    FROM supplier_rollups
    GROUP BY supplier_id, supplier_name
    ORDER BY total_spent DESC, order_count DESC, supplier_name ASC
");
$performanceStmt->execute($dateParams);
$supplierPerformance = $performanceStmt->fetchAll(PDO::FETCH_ASSOC);

$averageOrderValue = $totalOrders > 0 ? $totalExpenditure / $totalOrders : 0;
$hasDateFilter = isset($_GET['from_date']) || isset($_GET['to_date']);

include '../includes/header.php';
?>

<style>
    @media print {
        body {
            background: #ffffff !important;
        }

        nav,
        footer,
        iframe,
        #logoutModal,
        .no-print {
            display: none !important;
        }

        main {
            max-width: none !important;
            padding: 0 !important;
        }

        .print-page {
            padding: 0 !important;
        }

        .print-card {
            box-shadow: none !important;
            border: 1px solid #e5e7eb !important;
            break-inside: avoid;
        }

        .print-table {
            overflow: visible !important;
        }

        table {
            font-size: 11px !important;
        }

        th,
        td {
            padding: 10px !important;
        }
    }
</style>

<section class="print-page space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <p class="text-[11px] font-black text-blue-600 uppercase tracking-[0.28em] mb-3">Operational Intelligence</p>
            <h2 class="text-4xl md:text-5xl font-black text-gray-950 uppercase tracking-tight leading-none">Reports</h2>
            <p class="mt-3 text-sm font-semibold text-gray-500">
                <?= formatReportDate($fromDate) ?> to <?= formatReportDate($toDate) ?>
            </p>
        </div>

        <div class="no-print flex flex-wrap items-center gap-3">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-950 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg shadow-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                </svg>
                Print Report
            </button>
        </div>
    </div>

    <form method="GET" action="reports.php" class="no-print bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-6 md:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr_auto] gap-5 items-end">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">From Date</label>
                <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>"
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all text-sm font-bold text-gray-800">
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 ml-1">To Date</label>
                <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>"
                    class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all text-sm font-bold text-gray-800">
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 lg:flex-none px-7 py-4 bg-blue-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                    Apply Range
                </button>
                <?php if ($hasDateFilter): ?>
                    <a href="reports.php"
                        class="flex-1 lg:flex-none px-7 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-gray-200 transition-all text-center">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="print-card bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
            <div class="flex items-start justify-between gap-5">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.22em] mb-3">Total Orders</p>
                    <h3 class="text-4xl font-black text-gray-950 tracking-tight"><?= number_format($totalOrders) ?></h3>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6M9 12h6m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="print-card bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
            <div class="flex items-start justify-between gap-5">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.22em] mb-3">Items Received</p>
                    <h3 class="text-4xl font-black text-gray-950 tracking-tight"><?= number_format($itemsReceived) ?></h3>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4L4 7m0 0v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="print-card bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
            <div class="flex items-start justify-between gap-5">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.22em] mb-3">Total Expenditure</p>
                    <h3 class="text-3xl font-black text-gray-950 tracking-tight">PHP <?= number_format($totalExpenditure, 2) ?></h3>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-8V6m0 10v2m8-6a8 8 0 11-16 0 8 8 0 0116 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="print-card bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.22em] mb-3">Average Order Value</p>
            <h3 class="text-2xl font-black text-gray-950">PHP <?= number_format($averageOrderValue, 2) ?></h3>
            <p class="text-xs font-bold text-gray-400 mt-3">Based on received expenditure across all orders in range.</p>
        </div>

        <div class="print-card lg:col-span-3 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-8 py-7 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-black text-gray-950 uppercase tracking-tight">Supplier Performance</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Received transactions only</p>
                </div>
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] bg-blue-50 px-4 py-2 rounded-full w-fit">
                    <?= count($supplierPerformance) ?> suppliers
                </span>
            </div>

            <div class="print-table overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 uppercase text-[10px] font-black tracking-[0.18em]">
                            <th class="px-8 py-4 border-b border-gray-100">Supplier Name</th>
                            <th class="px-8 py-4 border-b border-gray-100 text-center">Order Count</th>
                            <th class="px-8 py-4 border-b border-gray-100 text-right">Total Spent</th>
                            <th class="px-8 py-4 border-b border-gray-100 text-right">Last Transaction</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($supplierPerformance as $supplier): ?>
                            <tr class="border-b border-gray-50 last:border-0 hover:bg-blue-50/30 transition">
                                <td class="px-8 py-5 font-black text-gray-900">
                                    <?= htmlspecialchars($supplier['supplier_name'] ?? 'Unknown Supplier') ?>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex min-w-12 justify-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-black">
                                        <?= number_format((int)($supplier['order_count'] ?? 0)) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right font-black text-gray-950">
                                    PHP <?= number_format((float)($supplier['total_spent'] ?? 0), 2) ?>
                                </td>
                                <td class="px-8 py-5 text-right font-bold text-gray-500">
                                    <?= formatReportDate($supplier['last_transaction_date'] ?? '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($supplierPerformance)): ?>
                            <tr>
                                <td colspan="4" class="px-8 py-14 text-center">
                                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No received supplier activity found</p>
                                    <p class="text-xs font-semibold text-gray-400 mt-2">Try expanding the report date range.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="hidden print:block text-xs text-gray-500 font-semibold pt-4">
        Generated by <?= htmlspecialchars($_SESSION['username'] ?? 'Tracker Pro') ?> on <?= date('M d, Y h:i A') ?>.
    </div>
</section>

<?php include '../includes/footer.php'; ?>
