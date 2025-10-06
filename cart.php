<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$cart_items = $conn->query("SELECT c.*, p.name, p.price, p.images FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Etsy Clone</title>
    <style>
        /* Cart CSS: Table with totals, remove buttons with animations. */
        body { font-family: 'Arial', sans-serif; background: #f9f9f9; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); animation: fadeIn 1s; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        img { max-width: 50px; border-radius: 5px; }
        button { background: #F1641D; color: white; border: none; padding: 8px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #d14e14; }
        .total { font-weight: bold; text-align: right; padding: 20px; }
        @media (max-width: 768px) { table { font-size: 14px; } }
    </style>
</head>
<body>
    <h2>Your Cart</h2>
    <table>
        <tr><th>Image</th><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>
        <?php while ($item = $cart_items->fetch_assoc()): 
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
        ?>
            <tr>
                <td><img src="<?php echo json_decode($item['images'])[0] ?? 'placeholder.jpg'; ?>" alt="<?php echo $item['name']; ?>"></td>
                <td><?php echo $item['name']; ?></td>
                <td>$<?php echo $item['price']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo $subtotal; ?></td>
                <td><button onclick="removeFromCart(<?php echo $item['id']; ?>)">Remove</button></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <div class="total">Total: $<?php echo $total; ?></div>
    <button onclick="location.href='checkout.php'" style="background: #F1641D; color: white; padding: 15px; width: 200px; margin: 20px auto; display: block; border-radius: 5px;">Proceed to Checkout</button>
    <a href="index.php" style="display: block; text-align: center; color: #F1641D;">Continue Shopping</a>
    <script>
        function removeFromCart(cartId) {
            if (confirm('Remove item?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'cart_id=' + cartId
                }).then(response => response.text()).then(data => {
                    alert(data);
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>
