<?php
session_start();
require 'db.php';

$error = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        $db_hash = trim($user_data['password']);

        // Check password
        if (md5($pass) === $db_hash) {
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['username'] = $user_data['username'];

            $role = $user_data['role'];

            switch ($role) {
                case 'Admin':
                    header("Location: ../modules/dashboard.php");
                    break;
                case 'Order_Staff':
                    header("Location: ../modules/order_list.php");
                    break;
                case 'Product_Staff':
                    header("Location: ../modules/product_list.php");
                    break;
                case 'Supplier_Staff':
                    header("Location: ../modules/supplier_list.php");
                    break;
                default:
                    header("Location: ../modules/dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<div class="login-card"> 
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm font-bold">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        </form>
</div>