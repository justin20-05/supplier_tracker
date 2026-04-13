<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_toast = false;

$user_role = $_SESSION['role'] ?? 'Staff';
$back_link = 'dashboard.php';

switch ($user_role) {
    case 'Supplier_Staff': $back_link = 'supplier_list.php'; break;
    case 'Product_Staff':  $back_link = 'product_list.php';  break;
    case 'Order_Staff':    $back_link = 'order_list.php';    break;
    case 'Admin':          $back_link = 'dashboard.php';     break;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_initial = strtoupper(trim($_POST['middle_initial']));
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, middle_initial = ?, last_name = ?, username = ? WHERE user_id = ?");
        $stmt->execute([$first_name, $middle_initial, $last_name, $username, $user_id]);
        
        // Update session
        $_SESSION['username'] = $username;
        $success_toast = true;
    } catch (PDOException $e) {
        $error_msg = "Username already taken.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto mt-10 mb-20">
    <div class="mb-6">
        <a href="<?= $back_link ?>" class="inline-flex items-center text-xs font-black text-gray-400 uppercase tracking-[0.2em] hover:text-blue-600 transition-colors group">
            <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-end px-10">
            <div class="w-24 h-24 rounded-3xl bg-white shadow-xl translate-y-8 flex items-center justify-center text-blue-600 border-4 border-white">
                <span class="text-4xl font-black"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
            </div>
        </div>

        <div class="p-10 pt-16">
            <div class="mb-8">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Account Profile</h2>
                <p class="text-gray-500 font-medium italic">Role: <span class="text-blue-600 font-bold"><?= str_replace('_', ' ', $user['role']) ?></span></p>
            </div>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-5 gap-4">
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">First Name</label>
                        <input type="text" name="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>" 
                            class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1 text-center">M.I.</label>
                        <input type="text" name="middle_initial" maxlength="1" value="<?= htmlspecialchars($user['middle_initial']) ?>" 
                            class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800 text-center">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Last Name</label>
                        <input type="text" name="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>" 
                            class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-[0.15em] mb-2 ml-1">Username</label>
                    <input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>" 
                        class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold text-gray-800">
                </div>

                <div class="bg-blue-50/50 p-6 rounded-2xl border border-blue-100/50">
                    <p class="text-xs text-blue-600 font-bold mb-1 uppercase tracking-widest text-center">Contact Admin to change password.</p>
                </div>

                <div class="pt-4 flex flex-col md:flex-row gap-4">
                    <button type="submit" class="flex-1 py-4 bg-blue-600 text-white rounded-2xl font-black text-sm hover:bg-blue-700 transition-all uppercase tracking-widest">
                        Save Changes
                    </button>
                    <a href="<?= $back_link ?>" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-sm hover:bg-gray-200 transition-all uppercase tracking-widest text-center">
                        Discard
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>