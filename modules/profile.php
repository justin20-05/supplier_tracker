<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_toast = false;
$error_msg = null;

$user_role = $_SESSION['role'] ?? 'Staff';
$back_link = 'dashboard.php';

switch ($user_role) {
    case 'Supplier_Staff':
        $back_link = 'supplier_list.php';
        break;
    case 'Product_Staff':
        $back_link = 'product_list.php';
        break;
    case 'Order_Staff':
        $back_link = 'order_list.php';
        break;
    case 'Admin':
        $back_link = 'dashboard.php';
        break;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_initial = strtoupper(trim($_POST['middle_initial']));
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, middle_initial = ?, last_name = ?, username = ? WHERE user_id = ?");
        $stmt->execute([$first_name, $middle_initial, $last_name, $username, $user_id]);

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

<div class="max-w-6xl mx-auto mt-8 mb-20 px-4">
    <div class="flex justify-between items-center mb-8">
        <a href="<?= $back_link ?>" class="inline-flex items-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-blue-600 transition-all group">
            <div class="w-8 h-8 rounded-xl bg-white border border-gray-100 flex items-center justify-center mr-3 shadow-sm group-hover:border-blue-200 group-hover:bg-blue-50 transition-all">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </div>
            Exit Profile
        </a>
        <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-full border border-emerald-100">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Active Session</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 rounded-[2.5rem] p-8 relative overflow-hidden shadow-2xl shadow-blue-900/20">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white mb-6 shadow-lg shadow-blue-500/30">
                        <span class="text-4xl font-black italic"><?= strtoupper(substr($user['first_name'], 0, 1)) ?></span>
                    </div>

                    <h1 class="text-3xl font-black text-white tracking-tight mb-1">
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                    </h1>
                    <p class="text-blue-400 font-bold text-sm tracking-wide mb-6">@<?= htmlspecialchars($user['username']) ?></p>

                    <div class="space-y-4 pt-6 border-t border-white/10">
                        <div>
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-1">System Privilege</p>
                            <span class="inline-block px-4 py-1.5 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-gray-300">
                                <?= str_replace('_', ' ', $user['role']) ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-1">Account Created</p>
                            <p class="text-xs font-bold text-gray-400"><?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Privacy Note</p>
                        <p class="text-[11px] text-gray-500 font-medium leading-tight">Credentials managed by system admin.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm relative overflow-hidden flex flex-col h-full">

                <div class="px-10 py-8 border-b border-gray-50 bg-gray-50/30">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Account Configuration</h3>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-widest mt-1">Update your public-facing identity</p>
                </div>

                <div class="p-10 flex-grow">
                    <?php if ($error_msg): ?>
                        <div class="mb-8 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-xs font-bold flex items-center animate-shake">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                            </svg>
                            <?= $error_msg ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start border-b border-gray-50 pb-8">
                            <div>
                                <h4 class="text-sm font-black text-gray-800 tracking-tight">Legal Name</h4>
                                <p class="text-xs text-gray-400 mt-1 leading-relaxed">Ensure this matches your official identification for audit purposes.</p>
                            </div>
                            <div class="md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                    <div class="md:col-span-5">
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">First Name</label>
                                        <input type="text" name="first_name" placeholder="e.g. John" required value="<?= htmlspecialchars($user['first_name']) ?>"
                                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-4 focus:ring-blue-500/5 focus:bg-white focus:border-blue-500 transition-all font-bold text-gray-800 text-sm">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1 text-center">M.I.</label>
                                        <input type="text" name="middle_initial" placeholder="--" maxlength="1" value="<?= htmlspecialchars($user['middle_initial']) ?>"
                                            class="w-full px-2 py-3.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-4 focus:ring-blue-500/5 focus:bg-white focus:border-blue-500 transition-all font-bold text-gray-800 text-sm text-center uppercase">
                                    </div>

                                    <div class="md:col-span-5">
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Last Name</label>
                                        <input type="text" name="last_name" placeholder="e.g. Doe" required value="<?= htmlspecialchars($user['last_name']) ?>"
                                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-4 focus:ring-blue-500/5 focus:bg-white focus:border-blue-500 transition-all font-bold text-gray-800 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start border-b border-gray-50 pb-8">
                            <div>
                                <h4 class="text-sm font-black text-gray-800 tracking-tight">Username</h4>
                                <p class="text-xs text-gray-400 mt-1 leading-relaxed">Unique identifier used for logging into the platform.</p>
                            </div>
                            <div class="md:col-span-2">
                                <div class="relative max-w-sm">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>"
                                        class="w-full pl-12 pr-6 py-3.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-4 focus:ring-blue-500/5 focus:bg-white focus:border-blue-500 transition-all font-bold text-gray-800 text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <div class="hidden sm:block">
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                    </svg>
                                    Information is secure
                                </p>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button type="button" onclick="window.location.reload()" class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 sm:flex-none px-10 py-3.5 bg-slate-900 text-white rounded-xl font-black text-xs hover:bg-blue-600 transition-all uppercase tracking-[0.2em] shadow-lg shadow-slate-900/10">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($success_toast): ?>
    <script>
        showToast("Profile identity updated", "success");
    </script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>