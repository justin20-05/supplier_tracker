<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'Staff';

if ($current_page === 'dashboard.php' && $user_role !== 'Admin') {
    if ($user_role === 'Supplier_Staff') {
        header("Location: supplier_list.php");
    } elseif ($user_role === 'Order_Staff') {
        header("Location: order_list.php");
    } elseif ($user_role === 'Product_Staff') {
        header("Location: product_list.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Supplier Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/header-style.css">
    <script src="../javascript/toast.js"></script>
</head>

<body class="bg-gray-50">
    <div id="logoutModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-2 tracking-tight">End Session?</h3>
                    <p class="text-gray-500 text-sm font-medium mb-8">Are you sure you want to log out?</p>

                    <div class="flex gap-3">
                        <button onclick="toggleLogoutModal()" class="flex-1 py-4 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">
                            Cancel
                        </button>
                        <a href="../modules/logout.php" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-4 rounded-2xl text-sm font-bold shadow-lg shadow-red-200 transition-all uppercase tracking-widest text-center">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="bg-white shadow-sm mb-8 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">

            <div class="flex items-center space-x-10">
                <h1 class="font-black text-xl text-blue-600 tracking-tighter uppercase">Tracker Pro</h1>

                <div class="hidden md:flex items-center space-x-8 text-[13px] font-bold uppercase tracking-wider">
                    
                    <?php if ($user_role === 'Admin'): ?>
                    <a href="../modules/dashboard.php"
                        class="nav-link <?= ($current_page == 'dashboard.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Dashboard
                    </a>
                    <?php endif; ?>

                    <?php if ($user_role === 'Admin' || $user_role === 'Supplier_Staff'): ?>
                    <a href="../modules/supplier_list.php"
                        class="nav-link <?= ($current_page == 'supplier_list.php' || $current_page == 'add_supplier.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Suppliers
                    </a>
                    <?php endif; ?>

                    <?php if ($user_role === 'Admin' || $user_role === 'Product_Staff'): ?>
                    <a href="../modules/product_list.php"
                        class="nav-link <?= ($current_page == 'product_list.php' || $current_page == 'add_product.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Products
                    </a>
                    <?php endif; ?>

                    <?php if ($user_role === 'Admin' || $user_role === 'Order_Staff'): ?>
                    <a href="../modules/order_list.php"
                        class="nav-link <?= ($current_page == 'order_list.php' || $current_page == 'add_order.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                        Orders
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="relative" id="settingsDropdown">
                    <button onclick="toggleDropdown()"
                        class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm active:scale-95 group">
                        <svg class="w-6 h-6 transition-transform duration-500 ease-in-out" id="gearIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>

                    <div id="dropdownMenu" class="hidden absolute right-0 mt-3 w-60 bg-white rounded-3xl shadow-2xl border border-gray-100 py-3 z-50 transform origin-top-right transition-all animate-in fade-in zoom-in duration-200">
                        <div class="px-6 py-3 mb-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Settings</p>
                        </div>

                        <a href="../modules/profile.php" class="flex items-center space-x-3 px-6 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors group/item">
                            <div class="p-2 rounded-lg bg-gray-50 group-hover/item:bg-blue-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold">My Profile</span>
                        </a>

                        <?php if ($user_role === 'Admin'): ?>
                            <a href="../modules/user_management.php" class="flex items-center space-x-3 px-6 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors group/item">
                                <div class="p-2 rounded-lg bg-gray-50 group-hover/item:bg-blue-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold">User Management</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="h-8 w-[1px] bg-gray-100 hidden sm:block mx-1"></div>

                <button type="button" onclick="toggleLogoutModal()"
                    class="bg-red-50 hover:bg-red-100 text-red-600 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-red-100 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </nav>

    <script>
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            const gear = document.getElementById('gearIcon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                gear.classList.add('rotate-180');
                setTimeout(() => {
                    window.addEventListener('click', closeMenuOnOutsideClick);
                }, 10);
            } else {
                closeDropdown();
            }
        }

        function closeDropdown() {
            const menu = document.getElementById('dropdownMenu');
            const gear = document.getElementById('gearIcon');
            if (menu) menu.classList.add('hidden');
            if (gear) gear.classList.remove('rotate-180');
            window.removeEventListener('click', closeMenuOnOutsideClick);
        }

        function closeMenuOnOutsideClick(e) {
            const container = document.getElementById('settingsDropdown');
            if (container && !container.contains(e.target)) {
                closeDropdown();
            }
        }

        function toggleLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }
    </script>

    <main class="max-w-6xl mx-auto px-4">