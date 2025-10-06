<?php
session_start();
include 'db.php';

// Fetch featured (recent) and trending (high views) products
$featured = $conn->query("SELECT p.*, u.username AS seller, c.name AS category FROM products p JOIN users u ON p.seller_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.deleted_at IS NULL ORDER BY p.created_at DESC LIMIT 4");
$trending = $conn->query("SELECT p.*, u.username AS seller, c.name AS category FROM products p JOIN users u ON p.seller_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.deleted_at IS NULL ORDER BY p.views DESC LIMIT 4");

// Update views for displayed products
if ($featured->num_rows > 0) {
    while ($row = $featured->fetch_assoc()) {
        $conn->query("UPDATE products SET views = views + 1 WHERE id = " . $row['id']);
    }
    $featured->data_seek(0); // Reset pointer
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etsy Clone - Homepage</title>
    <style>
        /* Stunning CSS: Etsy-inspired with vibrant orange (#F1641D), animations, and responsive design */
        * { box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; background: linear-gradient(to bottom, #fff, #f7f7f7); color: #333; }
        header { background: linear-gradient(135deg, #F1641D, #ff8c4d); color: white; padding: 40px; text-align: center; box-shadow: 0 6px 15px rgba(0,0,0,0.2); animation: fadeIn 1s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        nav { display: flex; justify-content: space-around; background: #fff; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        nav a { color: #F1641D; text-decoration: none; font-weight: bold; font-size: 18px; padding: 10px; transition: color 0.3s, transform 0.3s; }
        nav a:hover { color: #d14e14; transform: scale(1.1); }
        .section { padding: 40px 20px; max-width: 1200px; margin: auto; }
        .section h2 { font-size: 30px; text-align: center; color: #F1641D; margin-bottom: 30px; animation: slideUp 1s; }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .product { background: white; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.15); padding: 20px; text-align: center; transition: transform 0.4s, box-shadow 0.4s; position: relative; overflow: hidden; }
        .product:hover { transform: translateY(-8px); box-shadow: 0 12px 25px rgba(0,0,0,0.25); }
        .product img { max-width: 100%; height: 220px; object-fit: cover; border-radius: 8px; transition: transform 0.5s; }
        .product:hover img { transform: scale(1.05); }
        .product h3 { font-size: 22px; margin: 15px 0 10px; color: #333; }
        .product p { font-size: 14px; color: #666; margin: 5px 0; }
        .product .price { font-weight: bold; color: #F1641D; font-size: 20px; }
        .product .rating { color: #f1c40f; font-size: 16px; }
        button { background: #F1641D; color: white; border: none; padding: 12px 25px; border-radius: 25px; cursor: pointer; font-size: 16px; transition: background 0.3s, transform 0.3s; }
        button:hover { background: #d14e14; transform: scale(1.05); }
        .badge { position: absolute; top: 10px; left: 10px; background: #f1c40f; color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        footer { background: linear-gradient(135deg, #F1641D, #ff8c4d); color: white; text-align: center; padding: 20px; margin-top: 40px; }
        @media (max-width: 768px) {
            .product-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
            nav { flex-direction: column; text-align: center; }
            nav a { margin: 5px 0; }
            .product img { height: 160px; }
        }
        @media (max-width: 480px) {
            .section { padding: 20px 10px; }
            .product { padding: 15px; }
            button { padding: 10px; font-size: 14px; }
            .section h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to Etsy Clone</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! 
                <a href="javascript:void(0)" onclick="window.location.href='profile.php'" style="color: white;">Profile</a> | 
                <a href="javascript:void(0)" onclick="window.location.href='logout.php'" style="color: white;">Logout</a></p>
        <?php else: ?>
            <p><a href="javascript:void(0)" onclick="window.location.href='login.php'" style="color: white;">Login</a> | 
               <a href="javascript:void(0)" onclick="window.location.href='signup.php'" style="color: white;">Signup</a></p>
        <?php endif; ?>
    </header>
    <nav>
        <a href="javascript:void(0)" onclick="window.location.href='index.php'">Home</a>
        <a href="javascript:void(0)" onclick="window.location.href='products.php'">Browse Products</a>
        <a href="javascript:void(0)" onclick="window.location.href='cart.php'">Cart</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller'): ?>
            <a href="javascript:void(0)" onclick="window.location.href='add_product.php'">Add Product</a>
        <?php endif; ?>
    </nav>
    <section class="section">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <?php if ($featured->num_rows > 0): ?>
                <?php while ($row = $featured->fetch_assoc()): ?>
                    <div class="product">
                        <span class="badge">New</span>
                        <img src="<?php echo htmlspecialchars(json_decode($row['images'])[0] ?? 'uploads/products/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($row['description'], 0, 100) . (strlen($row['description']) > 100 ? '...' : '')); ?></p>
                        <p class="price">$<?php echo number_format($row['price'], 2); ?></p>
                        <p>Category: <?php echo htmlspecialchars($row['category']); ?></p>
                        <p>Seller: <?php echo htmlspecialchars($row['seller']); ?></p>
                        <p class="rating">Rating: <?php echo number_format($row['average_rating'], 1); ?>/5 ★</p>
                        <button onclick="addToCart(<?php echo $row['id']; ?>)">Add to Cart</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No featured products available.</p>
            <?php endif; ?>
        </div>
    </section>
    <section class="section">
        <h2>Trending Products</h2>
        <div class="product-grid">
            <?php if ($trending->num_rows > 0): ?>
                <?php while ($row = $trending->fetch_assoc()): ?>
                    <div class="product">
                        <span class="badge">Trending</span>
                        <img src="<?php echo htmlspecialchars(json_decode($row['images'])[0] ?? 'uploads/products/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($row['description'], 0, 100) . (strlen($row['description']) > 100 ? '...' : '')); ?></p>
                        <p class="price">$<?php echo number_format($row['price'], 2); ?></p>
                        <p>Category: <?php echo htmlspecialchars($row['category']); ?></p>
                        <p>Seller: <?php echo htmlspecialchars($row['seller']); ?></p>
                        <p class="rating">Rating: <?php echo number_format($row['average_rating'], 1); ?>/5 ★</p>
                        <button onclick="addToCart(<?php echo $row['id']; ?>)">Add to Cart</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No trending products available.</p>
            <?php endif; ?>
        </div>
    </section>
    <footer>&copy; 2025 Etsy Clone. All rights reserved.</footer>
    <script>
        // JavaScript for add to cart and redirections
        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId + '&quantity=1'
            }).then(response => response.text()).then(data => {
                alert(data);
                // Optional: Animate button or update cart count
            }).catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
