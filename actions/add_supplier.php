<?php
require '../config/db.php';
include '../includes/header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize basic input
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

        header("Location: ../modules/supplier_list.php?msg=added");
        exit();
        
        $message = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Supplier added successfully!</div>";
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add New Supplier</h2>
        <a href="../modules/supplier_list.php" class="text-blue-600 hover:underline">← Back to List</a>
    </div>

    <?= $message ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Vendor Name</label>
            <input type="text" name="name" required class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                <input type="text" name="contact_person" class="w-full p-2 border rounded outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" class="w-full p-2 border rounded outline-none bg-white">
                    <option value="Electronics">Electronics</option>
                    <option value="Office Stationery">Office Stationery</option>
                    <option value="Logistics">Logistics</option>
                    <option value="Raw Materials">Raw Materials</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" required class="w-full p-2 border rounded outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" class="w-full p-2 border rounded outline-none">
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700 transition">
            Save Supplier
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>