<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm mb-8">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="font-bold text-xl text-blue-600">Tracker Pro</h1>
            <div class="space-x-6 text-gray-600">
                <a href="../modules/dashboard.php" class="hover:text-blue-600">Dashboard</a>
                <a href="../modules/supplier_list.php" class="hover:text-blue-600">Suppliers</a>
                <a href="../modules/product_list.php" class="hover:text-blue-600">Products</a> 
                <a href="../actions/logout.php" class="text-red-500">Logout</a>
            </div>
        </div>
    </nav>
    <main class="max-w-6xl mx-auto px-4">
    <?php if (isset($_GET['msg'])): ?>
    <div id="toast" class="fixed top-5 right-5 z-50 transform transition-all duration-500 translate-y-0">
        <?php 
            $msgType = $_GET['msg'];
            $bgColor = ($msgType == 'deleted') ? 'bg-red-500' : 'bg-green-500';
            $text = ($msgType == 'deleted') ? 'Supplier removed successfully.' : 'Action completed successfully.';
        ?>
        <div class="<?= $bgColor ?> text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-medium"><?= $text ?></span>
        </div>
    </div>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 2000);
    </script>
<?php endif; ?>