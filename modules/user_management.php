<?php
require '../config/db.php';
include '../includes/header.php';

if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

$query = "SELECT user_id, username, first_name, middle_initial, last_name, role, created_at FROM users ORDER BY role ASC, first_name ASC";
$users = $pdo->query($query)->fetchAll();

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">User Management</h2>
        <p class="text-gray-500 text-sm font-medium">Manage system access for Admins and Staff members</p>
    </div>
    <a href="../actions/add_user.php"
        class="flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-2xl font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create New Account
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
            <?php foreach ($users as $u): 
                $fullName = $u['first_name'] . ' ' . ($u['middle_initial'] ? $u['middle_initial'] . '. ' : '') . $u['last_name'];
            ?>
                <tr class="hover:bg-blue-50/20 transition border-b border-gray-50 last:border-0">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center font-black text-xs">
                                <?= strtoupper(substr($u['first_name'], 0, 1)) ?>
                            </div>
                            <span class="font-bold text-gray-800 tracking-tight"><?= htmlspecialchars($fullName) ?></span>
                        </div>
                    </td>
                    <td class="p-5 text-gray-700 font-bold">@<?= htmlspecialchars($u['username']) ?></td>
                    <td class="p-5">
                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider <?= $u['role'] === 'Admin' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600' ?>">
                            <?= str_replace('_', ' ', $u['role']) ?>
                        </span>
                    </td>
                    <td class="p-5 text-gray-800 font-medium"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td class="p-5">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" 
                                    class="view-btn p-2 text-green-500 bg-green-50 rounded-lg hover:bg-green-600 hover:text-white transition-all shadow-sm" 
                                    title="View Details"
                                    data-info='<?= htmlspecialchars(json_encode([
                                        "Full Name" => $fullName,
                                        "Username" => "@".$u['username'],
                                        "Role" => str_replace('_', ' ', $u['role']),
                                        "Account ID" => "#USR-".$u['user_id'],
                                        "Joined Date" => date('F d, Y', strtotime($u['created_at']))
                                    ])) ?>'>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            <a href="../actions/edit_user.php?id=<?= $u['user_id'] ?>"
                                class="p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit User">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            
                            <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                <button type="button" 
                                    onclick="openDeleteModal(<?= $u['user_id'] ?>, '<?= htmlspecialchars($fullName) ?>')"
                                    class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Remove User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-400 font-black text-[9px] uppercase tracking-widest px-3 py-2 rounded-lg">Current User</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="viewModal" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xl" onclick="closeViewModal()"></div>
    
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-xl p-6">
        <div class="bg-white rounded-[3rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] border border-white/20 overflow-hidden animate-in fade-in zoom-in duration-300">
            
            <div class="px-10 pt-10 pb-6 bg-gradient-to-b from-blue-50/50 to-transparent flex justify-between items-start">
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100/50 text-blue-700 text-[10px] font-black uppercase tracking-[0.2em] mb-3">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        User Profile
                    </div>
                    <h3 id="userNameDisplay" class="text-3xl font-black text-gray-900 tracking-tight leading-tight"></h3>
                    <p id="userHandleDisplay" class="text-blue-600/80 font-bold text-lg italic"></p>
                </div>
                <button onclick="closeViewModal()" class="group p-3 bg-white shadow-sm border border-gray-100 hover:border-gray-200 rounded-2xl text-gray-400 hover:text-gray-900 transition-all">
                    <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-10 pb-10">
                <div id="userModalContent" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    </div>

                <div class="mt-10 flex items-center gap-4">
                    <button onclick="closeViewModal()" class="flex-1 py-5 bg-gray-900 text-white rounded-[1.5rem] font-black hover:bg-blue-600 transition-all duration-300 shadow-xl shadow-gray-200 uppercase tracking-[0.2em] text-[10px]">
                        Close Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6">
        <div class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden text-center animate-in fade-in zoom-in duration-200">
            <div class="p-10">
                <div class="w-20 h-20 bg-red-100 text-red-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Remove User?</h3>
                <p class="text-gray-500 text-sm font-medium mb-8 leading-relaxed px-2">
                    Are you sure you want to remove <span id="deleteUserName" class="text-red-600 font-bold"></span>? This action cannot be undone.
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
    document.addEventListener('click', function(e) {
        const vBtn = e.target.closest('.view-btn');
        if (vBtn) {
            const data = JSON.parse(vBtn.getAttribute('data-info'));
            const content = document.getElementById('userModalContent');
            
            content.innerHTML = Object.entries(data).map(([key, value]) => `
                <div class="p-4 bg-gray-50 rounded-2xl">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">${key}</p>
                    <p class="font-bold text-gray-800 text-lg">${value}</p>
                </div>
            `).join('');
            
            document.getElementById('viewModal').classList.remove('hidden');
        }
    });

    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteUserName').textContent = name;
        document.getElementById('confirmDeleteLink').href = "../actions/delete_user.php?id=" + id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<script src="../javascript/toast.js"></script>

<?php include '../includes/footer.php'; ?>