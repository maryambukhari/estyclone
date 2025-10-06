<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    echo "<script>location.href = 'index.php';</script>";
    exit;
}
include 'db.php';

// Fetch categories
$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $seller_id = $_SESSION['user_id'];
    $images = [];

    // Handle multiple image uploads (advanced feature)
    if (isset($_FILES['images'])) {
        $target_dir = "uploads/products/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        foreach ($_FILES['images']['name'] as $key => $img_name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $target_file = $target_dir . basename($img_name);
                move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file);
                $images[] = $target_file;
            }
        }
    }

    $images_json = json_encode($images);
    $stmt = $conn->prepare("INSERT INTO products (seller_id, name, description, price, category_id, images, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdiss", $seller_id, $name, $description, $price, $category_id, $images_json, $stock);
    if ($stmt->execute()) {
        // Log audit (advanced)
        $product_id = $stmt->insert_id;
        $details = json_encode(['name' => $name, 'price' => $price]);
        $conn->query("INSERT INTO audit_logs (user_id, action, entity_id, details) VALUES ($seller_id, 'product_create', $product_id, '$details')");
        echo "<script>alert('Product added!'); location.href = 'products.php';</script>";
    } else {
        echo "<script>alert('Error adding product.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Etsy Clone</title>
    <style>
        /* Dynamic CSS: Form with file preview animation, responsive layout. */
        body { font-family: 'Arial', sans-serif; background: #f9f9f9; padding: 20px; }
        form { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); animation: fadeIn 1s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #F1641D; border-radius: 5px; }
        button { background: #F1641D; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #d14e14; }
        @media (max-width: 600px) { form { padding: 20px; } }
    </style>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <h2>Add New Product</h2>
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="number" name="price" placeholder="Price" step="0.01" required>
        <select name="category_id" required>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="stock" placeholder="Stock Quantity" required>
        <input type="file" name="images[]" multiple accept="image/*">
        <button type="submit">Add Product</button>
    </form>
    <a href="index.php" style="display: block; text-align: center; color: #F1641D;">Back to Home</a>
</body>
</html>
