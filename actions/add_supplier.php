<?php
require '../config/db.php';

// 1. PLACE LOGIC AND REDIRECTS AT THE VERY TOP
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact_person'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $category = $_POST['category'];

    try {
        $sql = "INSERT INTO suppliers (name, contact_person, email, phone, category) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $contact, $email, $phone, $category]);

        // Redirect works now because no HTML has been sent yet
        header("Location: ../modules/supplier_list.php?msg=added");
        exit();
        
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded-xl mb-4 font-bold text-sm'>Error: " . $e->getMessage() . "</div>";
    }
}

// 2. FETCH NECESSARY DATA
$all_cats = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();

// 3. NOW INCLUDE THE HEADER (THIS STARTS THE HTML OUTPUT)
include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mt-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Add New Supplier</h2>
            <p class="text-xs text-gray-400 font-medium">Register a new delivery partner</p>
        </div>
        <a href="../modules/supplier_list.php" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition">← Back to List</a>
    </div>

    <?= $message ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Supplier Name</label>
            <input type="text" name="name" placeholder="Legal Business Name" required 
                   class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Contact Person</label>
                <input type="text" name="contact_person" placeholder="Full Name" 
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Category</label>
                <select name="category" required 
                        class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <option value="" disabled selected>Select a category</option>
                    <?php foreach($all_cats as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category_name']) ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                <input type="email" name="email" placeholder="supplier@example.com" required 
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Phone Number</label>
                <input type="text" name="phone" placeholder="0900-123-4567" 
                       class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-100 hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all active:scale-[0.98]">
                Save Supplier Profile
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>