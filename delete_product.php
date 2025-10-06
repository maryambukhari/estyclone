<?php
session_start();
include 'db.php';

if (isset($_GET['id']) && $_SESSION['role'] == 'seller') {
    $product_id = $_GET['id'];
    $seller_id = $_SESSION['user_id'];
    // Soft delete
    $conn->query("UPDATE products SET deleted_at = NOW() WHERE id = $product_id AND seller_id = $seller_id");
    // Audit log
    $conn->query("INSERT INTO audit_logs (user_id, action, entity_id) VALUES ($seller_id, 'product_delete', $product_id)");
    echo "<script>alert('Product deleted!'); location.href = 'products.php';</script>";
} else {
    echo "<script>location.href = 'index.php';</script>";
}
?>
