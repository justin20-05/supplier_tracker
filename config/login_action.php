<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        $pass_input = trim($_POST['password']);
        $db_hash = trim($user_data['password']);

       
        if (md5($pass_input) === $db_hash) {
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
            die("Error: Invalid password provided.");
        }
    } else {

        die("Error: Username not found.");
    }
}
