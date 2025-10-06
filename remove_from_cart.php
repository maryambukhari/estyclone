<?php
session_start();
include 'db.php';

if (isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $user_id = $_SESSION['user_id'];
    $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    echo "Item removed!";
}
?>
