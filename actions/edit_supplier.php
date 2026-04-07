<?php
require '../config/db.php'; 
include '../includes/header.php'; 

$id = $_GET['id'] ?? null;
$message = "";

if (!$id) {
    header("Location: ../modules/supplier_list.php");
    exit();
}

// Fetch current data to populate the form
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    die("Supplier not found.");
}

// Update request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, category=? 
                WHERE supplier_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'], $_POST['contact_person'], 
            $_POST['email'], $_POST['phone'], 
            $_POST['category'], $id
        ]);
        
        $message = "<div class='bg-blue-100 text-blue-700 p-3 rounded mb-4'>Changes saved successfully!</div>";
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);
        $supplier = $stmt->fetch();
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Update failed: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Supplier</h2>
        <a href="../modules/supplier_list.php" class="text-gray-500 hover:underline">Cancel</a>
    </div>

    <?= $message ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Vendor Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($supplier['name']) ?>" required 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                <input type="text" name="contact_person" value="<?= htmlspecialchars($supplier['contact_person']) ?>" 
                       class="w-full p-2 border rounded outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" class="w-full p-2 border rounded outline-none bg-white">
                    <?php 
                    $cats = ["Electronics", "Office Stationery", "Logistics", "Raw Materials"];
                    foreach($cats as $cat) {
                        $selected = ($supplier['category'] == $cat) ? "selected" : "";
                        echo "<option value='$cat' $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($supplier['email']) ?>" required 
                       class="w-full p-2 border rounded outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($supplier['phone']) ?>" 
                       class="w-full p-2 border rounded outline-none">
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700 transition">
            Update Supplier
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>