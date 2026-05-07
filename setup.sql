-- Create Parcel Delivery Database
CREATE DATABASE IF NOT EXISTS parcel_db;
USE parcel_db;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Price zones table

CREATE TABLE IF NOT EXISTS price_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100),
    price_per_kg DECIMAL(10,2),
    base_price DECIMAL(10,2),
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO price_zones (zone_name, price_per_kg, base_price, description) VALUES
('Zone A - Local',        500,  1000, 'Within same city'),
('Zone B - Regional',    1200,  2500, 'Nearby states'),
('Zone C - National',    2000,  5000, 'Across the country'),
('Zone D - Remote',      3500,  8000, 'Hard to reach areas');

-- Create Parcels Table
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
    zone_id INT DEFAULT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
    status ENUM('Pending', 'Picked Up', 'In Transit', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (zone_id) REFERENCES price_zones(id)
);

-- Insert Sample User (email: demo@example.com, password: password123)
INSERT INTO users (name, email, password, role) VALUES 
('Demo User', 'demo@example.com', '$2y$12$tV5Cy1AmysuhU74cy34OHOrzGXi7D2dL6dPolzuaZ9NjvpcWfVlzu', 'admin');
