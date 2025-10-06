<?php
session_start();
include 'db.php';

// Advanced search and filters
$where = "WHERE p.deleted_at IS NULL";
$params = [];
$types = "";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (isset($_GET['category'])) {
    $category = (int)$_GET['category'];
    $where .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if (isset($_GET['min_price'])) {
    $min_price = (float)$_GET['min_price'];
    $where .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if (isset($_GET['max_price'])) {
    $max_price = (float)$_GET['max_price'];
    $where .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

if (isset($_GET['min_rating'])) {
    $min_rating = (float)$_GET['min_rating'];
    $where .= " AND p.average_rating >= ?";
    $params[] = $min_rating;
    $types .= "d";
}

$stmt = $conn->prepare("SELECT p.*, u.username AS seller, c.name AS category FROM products p JOIN users u ON p.seller_id = u.id JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC");
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Categories for filter
$categories = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products - Etsy Clone</title>
    <style>
        /* Advanced CSS: Filter sidebar with animations, grid layout, hover effects. */
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; background: #f9f9f9; }
        .container { display: flex; }
        .filters { width: 250px; padding: 20px; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: sticky; top: 0; height: 100vh; animation: slideInLeft 1s; }
        @keyframes slideInLeft { from { transform: translateX(-100%); } to { transform: translateX(0); } }
        .product-grid { flex: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .product { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 15px; text-align: center; transition: transform 0.3s; }
        .product:hover { transform: translateY(-5px); }
        .product img { max-width: 100%; border-radius: 8px; }
        button { background: #F1641D; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #d14e14; }
        form { margin-bottom: 20px; }
        input, select { width: 100%; padding: 8px; margin: 5px 0; }
        @media (max-width: 768px) { .container { flex-direction: column; } .filters { width: 100%; height: auto; position: relative; } }
    </style>
</head>
<body>
    <header style="background: #F1641D; color: white; padding: 20px; text-align: center;">
        <h1>Browse Products</h1>
        <a href="index.php" style="color: white;">Home</a>
    </header>
    <div class="container">
        <div class="filters">
            <h3>Filters</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name or description" value="<?php echo $_GET['search'] ?? ''; ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if (isset($_GET['category']) && $_GET['category'] == $cat['id']) echo 'selected'; ?>><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="min_price" placeholder="Min Price" step="0.01" value="<?php echo $_GET['min_price'] ?? ''; ?>">
                <input type="number" name="max_price" placeholder="Max Price" step="0.01" value="<?php echo $_GET['max_price'] ?? ''; ?>">
                <input type="number" name="min_rating" placeholder="Min Rating (1-5)" step="0.1" value="<?php echo $_GET['min_rating'] ?? ''; ?>">
                <button type="submit">Apply Filters</button>
            </form>
        </div>
        <div class="product-grid">
            <?php while ($row = $products->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo json_decode($row['images'])[0] ?? 'placeholder.jpg'; ?>" alt="<?php echo $row['name']; ?>">
                    <h3><?php echo $row['name']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <p>Price: $<?php echo $row['price']; ?></p>
                    <p>Category: <?php echo $row['category']; ?></p>
                    <p>Seller: <?php echo $row['seller']; ?></p>
                    <p>Rating: <?php echo $row['average_rating']; ?>/5</p>
                    <p>Stock: <?php echo $row['stock']; ?></p>
                    <button onclick="addToCart(<?php echo $row['id']; ?>)">Add to Cart</button>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller' && $row['seller_id'] == $_SESSION['user_id']): ?>
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="color: #F1641D;">Edit</a> |
                        <a href="delete_product.php?id=<?php echo $row['id']; ?>" style="color: red;" onclick="return confirm('Are you sure?');">Delete</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId + '&quantity=1'
            }).then(response => response.text()).then(data => {
                alert(data);
            });
        }
    </script>
</body>
</html>
