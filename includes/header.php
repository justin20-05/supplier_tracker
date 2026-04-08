<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Logic to determine the current active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Supplier Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .nav-link {
            position: relative;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }

        /* The blue line logic */
        .nav-link-active::after {
            content: '';
            position: absolute;
            bottom: -18px;
            /* Adjust based on your nav height */
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #2563eb;
            /* blue-600 */
            border-radius: 99px;
        }
    </style>
</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow-sm mb-8 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="font-black text-xl text-blue-600 tracking-tighter uppercase">Tracker Pro</h1>

            <div class="flex items-center space-x-8 text-sm font-bold uppercase tracking-widest">
                <a href="../modules/dashboard.php"
                    class="nav-link <?= ($current_page == 'dashboard.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                    Dashboard
                </a>

                <a href="../modules/supplier_list.php"
                    class="nav-link <?= ($current_page == 'supplier_list.php' || $current_page == 'add_supplier.php' || $current_page == 'add_category.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                    Suppliers
                </a>

                <a href="../modules/product_list.php"
                    class="nav-link <?= ($current_page == 'product_list.php' || $current_page == 'add_product.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                    Products
                </a>

                <a href="../modules/order_list.php"
                    class="nav-link <?= ($current_page == 'order_list.php') ? 'text-blue-600 nav-link-active' : 'text-gray-400 hover:text-gray-600' ?>">
                    Orders
                </a>

                <a href="../modules/logout.php" class="text-red-400 hover:text-red-600 transition ml-4">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4">