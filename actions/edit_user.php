<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'Admin') {
    header("Location: ../modules/dashboard.php");
    exit();
}

$id = $_GET['id'] ?? null;
$message = "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: ../modules/user_management.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_initial = strtoupper(trim($_POST['middle_initial']));
    $last_name = trim($_POST['last_name']);
    $role = $_POST['role'];
    $new_password = $_POST['password'];

    try {
        if (!empty($new_password)) {
            $hashed_password = md5($new_password); 
            $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, middle_initial = ?, last_name = ?, role = ?, password = ? WHERE user_id = ?");
            $updateStmt->execute([$first_name, $middle_initial, $last_name, $role, $hashed_password, $id]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, middle_initial = ?, last_name = ?, role = ? WHERE user_id = ?");
            $updateStmt->execute([$first_name, $middle_initial, $last_name, $role, $id]);
        }
        header("Location: ../modules/user_management.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-2xl mb-6'>Error updating user.</div>";
    }
}

include '../includes/header.php'; 
?>

<div class="max-w-xl mx-auto mt-10 px-4">
    <div class="mb-6">
        <a href="../modules/user_management.php" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
    </div>

    <div class="bg-white p-6 md:p-10 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-3xl font-black text-gray-900 mb-2 tracking-tight">Edit Account</h2>
        <p class="text-gray-500 mb-8 text-sm font-medium">Updating details for <span class="text-blue-600">@<?= htmlspecialchars($user['username']) ?></span></p>

        <?= $message ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-5 gap-4">
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">First Name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">M.I.</label>
                    <input type="text" name="middle_initial" maxlength="1" value="<?= htmlspecialchars($user['middle_initial']) ?>" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all text-center font-bold text-gray-800">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Role</label>
                <select name="role" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 font-bold text-gray-800"
                    <?= ($user['user_id'] == $_SESSION['user_id']) ? 'disabled' : '' ?>>
                    <option value="Admin" <?= ($user['role'] == 'Admin') ? 'selected' : '' ?>>System Admin</option>
                    <option value="Supplier_Staff" <?= ($user['role'] == 'Supplier_Staff') ? 'selected' : '' ?>>Supplier Staff</option>
                    <option value="Order_Staff" <?= ($user['role'] == 'Order_Staff') ? 'selected' : '' ?>>Order Staff</option>
                    <option value="Product_Staff" <?= ($user['role'] == 'Product_Staff') ? 'selected' : '' ?>>Product Staff</option>
                </select>
                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                    <input type="hidden" name="role" value="<?= $user['role'] ?>">
                <?php endif; ?>
            </div>

            <div class="pt-4 border-t border-gray-50">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">New Password</label>
                <div class="relative group">
                    <input type="password" id="passwordInput" name="password" placeholder="Leave blank to keep current"
                        class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold">
                    <button type="button" onclick="toggleVisibility()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-600 transition-colors">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100 uppercase tracking-widest text-sm">
                    Update Account
                </button>
                <a href="../modules/user_management.php" class="flex-1 text-center py-4 text-gray-500 font-bold hover:text-gray-800 transition text-sm uppercase tracking-widest">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleVisibility() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
        } else {
            input.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    }
</script>
<?php include '../includes/footer.php'; ?>