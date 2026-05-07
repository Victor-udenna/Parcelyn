# ParcelDelivery Application - Setup Instructions

## Prerequisites

- PHP 7.4+
- MySQL/MariaDB running locally
- XAMPP, LAMP, or similar (with MySQL included)

## Setup Steps

### 1. Start MySQL/XAMPP

**On macOS with XAMPP:**

```bash
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
```

Or use XAMPP Control Panel to start MySQL.

### 2. Create Database and Tables

Run the SQL setup script:

```bash
mysql -u root -p < setup.sql
```

When prompted, enter your MySQL password (usually blank for XAMPP default):

```bash
mysql -u root < setup.sql
```

Or manually import via phpMyAdmin:

- Go to `http://localhost/phpmyadmin`
- Click "New"
- Name: `parcel_db`
- Create
- Go to SQL tab and paste contents of `setup.sql`

### 3. Verify Database Connection

Edit `db.php` if needed:

- Host: `127.0.0.1` (not `localhost`)
- Database: `parcel_db`
- Username: `root`
- Password: `` (empty for XAMPP default)

### 4. Start PHP Server

```bash
cd /Users/macintosh/Desktop/Projects/parcel-delivery
php -S localhost:8000
```

### 5. Access the Application

- **Login**: http://localhost:8000
  - Email: `demo@example.com`
  - Password: `password123`
- **Track without login**: http://localhost:8000/track_parcel.php

## Troubleshooting

### "Connection failed" error

- Ensure MySQL is running: `mysql -u root -p` should connect
- Check `db.php` - host should be `127.0.0.1` not `localhost`

### "Table doesn't exist" error

- Re-run the SQL setup: `mysql -u root < setup.sql`
- Or use phpMyAdmin to import `setup.sql`

### "Access denied" error

- MySQL password may not be blank. Update `db.php` with correct credentials.
- Default XAMPP: username `root`, no password

## Features

- **Login/Logout**: Secure authentication with password hashing
- **Send Parcels**: Create and track shipments
- **Track Parcels**: Public tracking without login
- **Update Status**: Manage parcel delivery status
- **Dashboard**: View statistics and recent parcels
