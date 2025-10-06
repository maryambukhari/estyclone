<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$cart_items = $conn->query("SELECT c.*, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");

$total = 0;
while ($item = $cart_items->fetch_assoc()) {
    $total += $item['price'] * $item['quantity'];
}
$cart_items->data_seek(0); // Reset

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dummy payment: Simulate transaction
    $transaction_id = 'DUMMY_' . uniqid();
    $stmt = $conn->prepare("INSERT INTO orders (buyer_id, total, transaction_id, status) VALUES (?, ?, ?, 'completed')");
    $stmt->bind_param("ids", $user_id, $total, $transaction_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Add order items and clear cart
    while ($item = $cart_items->fetch_assoc()) {
        $oi_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $oi_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $oi_stmt->execute();
    }
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    // Audit log
    $details = json_encode(['total' => $total]);
    $conn->query("INSERT INTO audit_logs (user_id, action, entity_id, details) VALUES ($user_id, 'order_place', $order_id, '$details')");

    echo "<script>alert('Order placed successfully! Transaction ID: $transaction_id'); location.href = 'index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Etsy Clone</title>
    <style>
        /* Checkout CSS: Simple form with secure look, animations. */
        body { font-family: 'Arial', sans-serif; background: #f9f9f9; padding: 20px; }
        .checkout { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); animation: fadeIn 1s; }
        button { background: #F1641D; color: white; border: none; padding: 15px; width: 100%; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #d14e14; }
        .total { font-size: 20px; text-align: right; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="checkout">
        <h2>Checkout</h2>
        <p>Dummy Payment System: Click to complete transaction.</p>
        <div class="total">Total: $<?php echo $total; ?></div>
        <form method="POST">
            <button type="submit">Pay Now (Dummy)</button>
        </form>
        <a href="cart.php" style="display: block; text-align: center; color: #F1641D;">Back to Cart</a>
    </div>
</body>
</html>
