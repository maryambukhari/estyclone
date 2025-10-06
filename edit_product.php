<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    echo "<script>location.href = 'index.php';</script>";
    exit;
}
include 'db.php';

$product_id = $_GET['id'] ?? 0;
$product_query = $conn->query("SELECT * FROM products WHERE id = $product_id AND seller_id = {$_SESSION['user_id']} AND deleted_at IS NULL");
if (!$product = $product_query->fetch_assoc()) {
    echo "<script>alert('Product not found or unauthorized.'); location.href = 'products.php';</script>";
    exit;
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $images = json_decode($product['images']) ?? [];

    // Handle new images
    if (isset($_FILES['images'])) {
        $target_dir = "uploads/products/";
        foreach ($_FILES['images']['name'] as $key => $img_name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $target_file = $target_dir . basename($img_name);
                move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file);
                $images[] = $target_file;
            }
        }
    }

    $images_json = json_encode($images);
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, images=?, stock=? WHERE id=?");
    $stmt->bind_param("ssdissi", $name, $description, $price, $category_id, $images_json, $stock, $product_id);
    if ($stmt->execute()) {
        // Audit log
        $details = json_encode(['old_name' => $product['name'], 'new_name' => $name]);
        $conn->query("INSERT INTO audit_logs (user_id, action, entity_id, details) VALUES ({$_SESSION['user_id']}, 'product_edit', $product_id, '$details')");
        echo "<script>alert('Product updated!'); location.href = 'products.php';</script>";
    } else {
        echo "<script>alert('Error updating product.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Etsy Clone</title>
    <style>
        /* Similar to add_product, with image preview. */
        body { font-family: 'Arial', sans-serif; background: #f9f9f9; padding: 20px; }
        form { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); animation: fadeIn 1s; }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #F1641D; border-radius: 5px; }
        button { background: #F1641D; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #d14e14; }
        .current-images { display: flex; flex-wrap: wrap; }
        .current-images img { max-width: 100px; margin: 5px; border-radius: 5px; }
    </style>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <h2>Edit Product</h2>
        <input type="text" name="name" value="<?php echo $product['name']; ?>" required>
        <textarea name="description" required><?php echo $product['description']; ?></textarea>
        <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
        <select name="category_id" required>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $product['category_id']) echo 'selected'; ?>><?php echo $cat['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
        <div class="current-images">
            <?php foreach (json_decode($product['images']) ?? [] as $img): ?>
                <img src="<?php echo $img; ?>" alt="Current Image">
            <?php endforeach; ?>
        </div>
        <input type="file" name="images[]" multiple accept="image/*"> (Add more images)
        <button type="submit">Update Product</button>
    </form>
    <a href="products.php" style="display: block; text-align: center; color: #F1641D;">Back to Products</a>
</body>
</html>
