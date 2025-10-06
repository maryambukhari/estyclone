<?php
$host = "localhost"; // Assuming localhost; update if different host is needed
$dbname = "dbbdl63csckccm";
$username = "uczrllawgyzfy";
$password = "tmq3v2ylpxpl";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8 for proper encoding
$conn->set_charset("utf8mb4");
?>
