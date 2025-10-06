<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ? AND deleted_at IS NULL");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo "<script>location.href = 'index.php';</script>";
        } else {
            echo "<script>alert('Invalid password.');</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Etsy Clone</title>
    <style>
        /* Elegant CSS: Similar to signup, with pulse animation on button. */
        body { background: linear-gradient(to left, #F1641D, #ff8c4d); color: white; font-family: 'Arial', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        form { background: rgba(255,255,255,0.9); padding: 40px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.3); width: 300px; animation: fadeIn 1s; color: #333; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #F1641D; border-radius: 5px; transition: border 0.3s; }
        input:focus { border: 1px solid #d14e14; outline: none; }
        button { background: #F1641D; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        button:hover { background: #d14e14; }
        @media (max-width: 480px) { form { width: 90%; padding: 20px; } }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Login</h2>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>New here? <a href="signup.php" style="color: #F1641D;">Signup</a></p>
    </form>
</body>
</html>
