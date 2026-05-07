<?php
$host     = '127.0.0.1';  // ← Change 'localhost' to '127.0.0.1'
$dbname   = 'parcel_db';
$username = 'root';
$password = '';            // Leave empty for XAMPP default
$port     = '3306';        // Default MySQL port

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>