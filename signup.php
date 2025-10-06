<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    if ($stmt->execute()) {
        echo "<script>alert('Signup successful!'); location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Error: Username or email already exists.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Etsy Clone</title>
    <style>
        /* Stunning CSS: Gradient background, animated form inputs, responsive. */
        body { background: linear-gradient(to right, #F1641D, #ff8c4d); color: white; font-family: 'Arial', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        form { background: rgba(255,255,255,0.9); padding: 40px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.3); width: 300px; animation: fadeIn 1s; color: #333; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #F1641D; border-radius: 5px; transition: border 0.3s; }
        input:focus, select:focus { border: 1px solid #d14e14; outline: none; }
        button { background: #F1641D; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; transition: background 0.3s, transform 0.3s; }
        button:hover { background: #d14e14; transform: scale(1.05); }
        @media (max-width: 480px) { form { width: 90%; padding: 20px; } }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Signup</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
        </select>
        <button type="submit">Signup</button>
        <p>Already have an account? <a href="login.php" style="color: #F1641D;">Login</a></p>
    </form>
</body>
</html>
