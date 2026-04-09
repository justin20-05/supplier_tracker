<?php
require '../config/db.php';
include '../includes/header.php';

if ($_SESSION['role'] !== 'Admin') { header("Location: ../modules/dashboard.php"); exit(); }

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $role]);
        header("Location: ../modules/user_management.php?msg=user_added");
        exit();
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-2xl mb-6'>Username already exists.</div>";
    }
}
?>

<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">New User Account</h2>
        <?= $message ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                <input type="text" name="full_name" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                <input type="text" name="username" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                    <input type="password" name="password" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Role</label>
                    <select name="role" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Staff">Staff Member</option>
                        <option value="Admin">System Admin</option>
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

<?php include '../includes/footer.php'; ?>