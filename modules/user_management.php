<?php
require '../config/db.php';
include '../includes/header.php';

// Access Control: Only Admins should see this
if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$query = "SELECT user_id, username, full_name, role, created_at FROM users ORDER BY role ASC, full_name ASC";
$users = $pdo->query($query)->fetchAll();

// Handle Success Messages
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>

<div id="toast-container" class="fixed top-24 right-8 z-[110] flex flex-col gap-4 pointer-events-none">
    <?php if ($msg == 'deleted'): ?>
        <div class="alert-msg pointer-events-auto bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 min-w-[300px]">
            <div class="w-2 h-2 rounded-full bg-red-500"></div>
            <span class="text-sm font-bold">User removed successfully</span>
        </div>
    <?php elseif ($msg == 'updated'): ?>
        <div class="alert-msg pointer-events-auto bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 min-w-[300px]">
            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
            <span class="text-sm font-bold">Account details updated</span>
        </div>
    <?php elseif ($msg == 'user_added'): ?>
        <div class="alert-msg pointer-events-auto bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 min-w-[300px]">
            <div class="w-2 h-2 rounded-full bg-green-500"></div>
            <span class="text-sm font-bold">New user created</span>
        </div>
    <?php elseif ($err == 'self_delete'): ?>
        <div class="alert-msg pointer-events-auto bg-red-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[300px]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="text-sm font-bold">Cannot delete your own account</span>
        </div>
    <?php endif; ?>
</div>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">User Management</h2>
        <p class="text-gray-500 text-sm font-medium">Manage system access for Admins and Staff members</p>
    </div>
    <a href="../actions/add_user.php"
        class="flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
        + Create New Account
    </a>
</div>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-blue-50/50 text-gray-900 uppercase text-[10px] font-black tracking-[0.15em]">
                <th class="p-5 border-b border-gray-100">Full Name</th>
                <th class="p-5 border-b border-gray-100">Username</th>
                <th class="p-5 border-b border-gray-100">Role</th>
                <th class="p-5 border-b border-gray-100">Joined Date</th>
                <th class="p-5 border-b border-gray-100 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($users as $u): ?>
                <tr class="hover:bg-blue-50/20 transition border-b border-gray-50 last:border-0">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center font-black text-xs">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <span class="font-bold text-gray-800 tracking-tight"><?= htmlspecialchars($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="p-5 text-gray-700 font-bold">@<?= htmlspecialchars($u['username']) ?></td>
                    <td class="p-5">
                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider <?= $u['role'] === 'Admin' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600' ?>">
                            <?= $u['role'] ?>
                        </span>
                    </td>
                    <td class="p-5 text-gray-800 font-medium"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td class="p-5 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="../actions/edit_user.php?id=<?= $u['user_id'] ?>"
                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit User">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            
                            <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                <button type="button" 
                                    onclick="openDeleteModal(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['full_name']) ?>')"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Remove User">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            <?php else: ?>
                                <span class="text-gray-300 font-bold text-[10px] uppercase tracking-widest italic px-2">You</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="deleteModal" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden">
            <div class="p-10 text-center">
                <div class="w-20 h-20 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Remove User?</h3>
                <p class="text-gray-500 text-sm font-medium mb-8 leading-relaxed px-2">
                    Are you sure you want to remove <span id="deleteUserName" class="text-red-600 font-bold"></span>?
                </p>
                <div class="flex flex-col gap-2">
                    <a id="confirmDeleteLink" href="#" class="w-full bg-red-600 hover:bg-red-700 text-white py-4 rounded-2xl text-sm font-black shadow-lg shadow-red-100 transition-all uppercase tracking-widest text-center">
                        Confirm Delete
                    </a>
                    <button onclick="closeDeleteModal()" class="w-full py-4 text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-[0.2em]">
                        Keep Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Handles the Auto-Dismissing Overlay (Toast)
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-msg');
        alerts.forEach(alert => {
            alert.style.transition = "all 0.6s cubic-bezier(0.16, 1, 0.3, 1)";
            alert.style.transform = "translateX(20px)";
            alert.style.opacity = "0";
            
            requestAnimationFrame(() => {
                alert.style.transform = "translateX(0)";
                alert.style.opacity = "1";
            });

            setTimeout(() => {
                alert.style.transform = "translateX(100%)";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 600);
            }, 2500);
        });
    });

    function openDeleteModal(id, name) {
        document.getElementById('deleteUserName').textContent = name;
        document.getElementById('confirmDeleteLink').href = "../actions/delete_user.php?id=" + id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>