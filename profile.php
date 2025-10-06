<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

// Fetch user data
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    // Handle image upload (advanced: multiple images support, but single for profile)
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
        $profile_image = $target_file;
        $conn->query("UPDATE users SET bio = '$bio', profile_image = '$profile_image' WHERE id = $user_id");
    } else {
        $conn->query("UPDATE users SET bio = '$bio' WHERE id = $user_id");
    }
    echo "<script>alert('Profile updated!'); location.href = 'profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Etsy Clone</title>
    <style>
        /* Professional CSS: Card-like profile, with hover effects and animations. */
        body { font-family: 'Arial', sans-serif; background: #f9f9f9; color: #333; margin: 0; padding: 20px; }
        .profile-card { max-width: 600px; margin: auto; background: white; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); padding: 30px; animation: slideUp 1s; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        img { max-width: 150px; border-radius: 50%; border: 3px solid #F1641D; transition: transform 0.3s; }
        img:hover { transform: rotate(5deg); }
        form { display: flex; flex-direction: column; }
        textarea, input { padding: 10px; margin: 10px 0; border: 1px solid #F1641D; border-radius: 5px; }
        button { background: #F1641D; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #d14e14; }
        @media (max-width: 600px) { .profile-card { padding: 20px; } }
    </style>
</head>
<body>
    <div class="profile-card">
        <h2>Profile: <?php echo $user['username']; ?></h2>
        <img src="<?php echo $user['profile_image'] ?? 'default_avatar.jpg'; ?>" alt="Profile Image">
        <p>Email: <?php echo $user['email']; ?></p>
        <p>Role: <?php echo ucfirst($user['role']); ?></p>
        <p>Bio: <?php echo $user['bio'] ?? 'No bio yet.'; ?></p>
        <form method="POST" enctype="multipart/form-data">
            <textarea name="bio" placeholder="Update your bio"><?php echo $user['bio'] ?? ''; ?></textarea>
            <input type="file" name="profile_image" accept="image/*">
            <button type="submit">Update Profile</button>
        </form>
        <a href="index.php" style="color: #F1641D;">Back to Home</a>
    </div>
</body>
</html>
