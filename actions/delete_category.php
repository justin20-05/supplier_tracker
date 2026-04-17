<?php
require '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['role'] !== 'Admin') {
    header("Location: ../modules/dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 1. Get the name of the category the user wants to delete
        $nameStmt = $pdo->prepare("SELECT category_name FROM categories WHERE category_id = ?");
        $nameStmt->execute([$id]);
        $category = $nameStmt->fetch();

        if ($category) {
            $category_name = $category['category_name'];

            // 2. Check if any supplier is currently using this category name
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers WHERE category = ?");
            $checkStmt->execute([$category_name]);
            $usageCount = $checkStmt->fetchColumn();

            if ($usageCount > 0) {
                // Category is in use, redirect with error
                header("Location: ../modules/view_category.php?msg=error");
                exit();
            }

            // 3. If not in use, proceed with deletion
            $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
            $deleteStmt->execute([$id]);
            header("Location: ../modules/view_category.php?msg=deleted");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../modules/view_category.php?msg=error");
        exit();
    }
}
header("Location: ../modules/view_category.php");
exit();