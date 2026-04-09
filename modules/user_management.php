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
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">User Management</h2>
        <p class="text-gray-500 text-sm">Manage system access for Admins and Staff members</p>
    </div>
    <a href="../actions/add_user.php"
        class="flex items-center justify-center px-5 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
        + Create New Account
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-widest">
                <th class="p-4 border-b">Full Name</th>
                <th class="p-4 border-b">Username</th>
                <th class="p-4 border-b">Role</th>
                <th class="p-4 border-b">Joined Date</th>
                <th class="p-4 border-b text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php foreach ($users as $u): ?>
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-50 last:border-0">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-gray-600 font-medium">@<?= htmlspecialchars($u['username']) ?></td>
                    <td class="p-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $u['role'] === 'Admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $u['role'] ?>
                        </span>
                    </td>
                    <td class="p-4 text-gray-500"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td class="p-4 text-center">
                        <a href="../actions/edit_user.php?id=<?= $u['user_id'] ?>"
                            class="text-blue-600 font-bold text-[10px] uppercase tracking-wider hover:underline">
                            Edit
                        </a>
                        <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                            <button onclick="confirmDelete(<?= $u['user_id'] ?>)"
                                class="text-red-400 font-bold text-[10px] uppercase tracking-wider hover:text-red-600 transition-colors">
                                Remove
                            </button>
                        <?php else: ?>
                            <span class="text-gray-300 font-bold text-[10px] uppercase tracking-wider italic">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete(id) {
        if (confirm("Are you sure you want to remove this user? This will revoke their system access permanently.")) {
            window.location.href = "../actions/delete_user.php?id=" + id;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>