<?php
/**
 * ParcelDelivery - Database Setup Script
 * Run this once to create tables and seed initial data
 */

$host     = '127.0.0.1';
$username = 'root';
$password = '';
$dbname   = 'parcel_db';

echo "🔧 Setting up ParcelDelivery Database...\n\n";

// 1. Connect to MySQL (without database)
try {
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL\n";
} catch (PDOException $e) {
    die("❌ MySQL Connection Failed: " . $e->getMessage() . 
        "\n\n💡 Make sure MySQL is running and credentials are correct in this script.\n");
}

try {
    // 2. Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✅ Database created/verified\n";
    
    // 3. Select Database
    $pdo->exec("USE $dbname");
    echo "✅ Database selected\n";
    
    // 4. Create Users Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "✅ Users table created\n";
    
    // 5. Create Parcels Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parcels (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tracking_number VARCHAR(50) UNIQUE NOT NULL,
            sender_id INT NOT NULL,
            sender_name VARCHAR(100) NOT NULL,
            receiver_name VARCHAR(100) NOT NULL,
            receiver_address TEXT NOT NULL,
            receiver_phone VARCHAR(20),
            weight DECIMAL(8, 2),
            description TEXT,
            status ENUM('Pending', 'Picked Up', 'In Transit', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id),
            INDEX idx_tracking (tracking_number),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "✅ Parcels table created\n";
    
    // 6. Clear existing demo user if present
    $pdo->exec("DELETE FROM parcels WHERE sender_id IN (SELECT id FROM users WHERE email='demo@example.com')");
    $pdo->exec("DELETE FROM users WHERE email='demo@example.com'");
    
    // 7. Insert Demo User with proper password hash
    // Password: password123
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Demo User', 'demo@example.com', $passwordHash, 'admin']);
    echo "✅ Demo user created\n";
    
    // 8. Insert Sample Parcels
    $stmt = $pdo->prepare("
        INSERT INTO parcels 
        (tracking_number, sender_id, sender_name, receiver_name, receiver_address, receiver_phone, weight, description, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $sampleParcels = [
        ['SWP-DEMO0001', 1, 'Demo User', 'John Doe', '123 Main St, Lagos', '+2348012345678', 2.5, 'Electronics', 'In Transit'],
        ['SWP-DEMO0002', 1, 'Demo User', 'Jane Smith', '456 Oak Ave, Abuja', '+2348087654321', 1.2, 'Documents', 'Delivered'],
        ['SWP-DEMO0003', 1, 'Demo User', 'Bob Wilson', '789 Pine Rd, Kano', '+2349011223344', 3.8, 'Clothing', 'Pending'],
    ];
    
    foreach ($sampleParcels as $parcel) {
        $stmt->execute($parcel);
    }
    echo "✅ Sample parcels created\n";
    
    echo "\n";
    echo "════════════════════════════════════════════════════════\n";
    echo "✨ Setup Complete!\n";
    echo "════════════════════════════════════════════════════════\n\n";
    echo "📝 Login Credentials:\n";
    echo "   Email: demo@example.com\n";
    echo "   Password: password123\n\n";
    echo "🌐 Access the app:\n";
    echo "   http://localhost:8000\n\n";
    echo "💡 Tip: You can delete the 'setup.php' file after running this.\n";
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
