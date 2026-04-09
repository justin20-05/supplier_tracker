<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/header-style.css">
</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow-sm mb-8 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">

            <div class="flex items-center space-x-10">
                <h1 class="font-black text-xl text-blue-600 tracking-tighter uppercase">Tracker Pro</h1>

                <div class="hidden md:flex items-center space-x-8 text-[13px] font-bold uppercase tracking-wider">
                    <a href="../modules/dashboard.php"
                        class="nav-link <?= ($current_page == 'dashboard.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Dashboard
                    </a>
                    <a href="../modules/supplier_list.php"
                        class="nav-link <?= ($current_page == 'supplier_list.php' || $current_page == 'add_supplier.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Suppliers
                    </a>
                    <a href="../modules/product_list.php"
                        class="nav-link <?= ($current_page == 'product_list.php' || $current_page == 'add_product.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Products
                    </a>
                    <a href="../modules/order_list.php"
                        class="nav-link <?= ($current_page == 'order_list.php' || $current_page == 'add_order.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Orders
                    </a>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <div class="relative group">
                        <a href="../modules/user_management.php"
                            class="flex items-center space-x-3 px-3 py-2 rounded-2xl hover:bg-gray-50 transition-all border <?= ($current_page == 'user_management.php') ? 'border-blue-100 bg-blue-50/50' : 'border-transparent' ?>">
                            <div class="text-right hidden sm:block">
                                <p class="text-[10px] font-black text-gray-900 leading-none uppercase tracking-tighter">
                                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
                                </p>
                                <p class="text-[9px] font-bold text-blue-500 uppercase tracking-widest">System Admin</p>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200 group-hover:scale-105 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </a>
                        <span class="tooltip shadow-xl">Manage Accounts</span>
                    </div>
                <?php else: ?>
                    <div class="relative group">
                        <div class="flex items-center space-x-3 px-3 py-2">
                            <div class="text-right hidden sm:block">
                                <p class="text-[10px] font-black text-gray-900 leading-none uppercase tracking-tighter">
                                    <?= htmlspecialchars($_SESSION['username']) ?>
                                </p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Staff Account</p>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <span class="tooltip shadow-xl">Profile Info</span>
                    </div>
                <?php endif; ?>

                <div class="h-8 w-[1px] bg-gray-100 hidden sm:block mx-1"></div>

                <a href="../modules/logout.php"
                    class="bg-red-50 hover:bg-red-100 text-red-600 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-red-100 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </nav>
    <main class="max-w-6xl mx-auto px-4">