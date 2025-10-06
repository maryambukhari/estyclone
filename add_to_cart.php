<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Please login to add to cart.";
    exit;
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Check stock
$stock_query = $conn->query("SELECT stock FROM products WHERE id = $product_id AND deleted_at IS NULL");
if ($stock = $stock_query->fetch_assoc()['stock'] >= $quantity) {
    // Upsert to cart
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
    $stmt->execute();
    echo "Added to cart!";
} else {
    echo "Insufficient stock.";
}
?>
