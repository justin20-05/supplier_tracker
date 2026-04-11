<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Access Control
if ($_SESSION['role'] !== 'Admin') { 
    header("Location: ../modules/dashboard.php"); 
    exit(); 
}

$error_toast = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    
    $password = md5($_POST['password']); 
    
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $role]);
        
        header("Location: ../modules/user_management.php?msg=added");
        exit();
    } catch (PDOException $e) {
        $error_toast = "Username already exists.";
    }
}

include '../includes/header.php';
?>

<div class="max-w-xl mx-auto mt-10">
    <div class="mb-6">
        <a href="../modules/user_management.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
    </div>

    <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">New User Account</h2>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                <input type="text" name="full_name" required placeholder="John Doe" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                <input type="text" name="username" required placeholder="johndoe123" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Role</label>
                    <select name="role" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Admin">System Admin</option>
                        <option value="Supplier_Staff">Supplier Staff</option>
                        <option value="Order_Staff">Order Staff</option>
                        <option value="Product_Staff">Product Staff</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">Create Account</button>
                <a href="../modules/user_management.php" class="flex-1 text-center py-4 text-gray-500 font-bold hover:text-gray-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php if ($error_toast): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("<?= $error_toast ?>", "error");
    });
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>