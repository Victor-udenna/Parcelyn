# Parcelyn Project Documentation

## Project Overview

**Parcelyn** is a simple parcel delivery management system built with **PHP** and **MySQL**.

The application allows an authenticated user/admin to create parcel deliveries, calculate delivery pricing based on distance zones and parcel weight, generate invoices, update parcel delivery status, and allow public users to track parcels using a tracking number.

The project is built as a traditional PHP application using individual PHP files rather than a framework like Laravel or Symfony.

---

## Repository

```text
https://github.com/Victor-udenna/Parcelyn
```

---

## Technology Stack

| Area | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL / MariaDB |
| Database Access | PDO |
| Frontend | HTML, CSS, JavaScript |
| Authentication | PHP Sessions |
| Password Security | `password_hash()` / `password_verify()` |
| Server | PHP built-in server, XAMPP, LAMP, MAMP, or similar |

---

## Main Features

### 1. User Login

The system has a login page where a user can authenticate using email and password.

After successful login, the user session stores:

```php
$_SESSION['user_id']
$_SESSION['user_name']
$_SESSION['user_role']
```

Authenticated users are redirected to the dashboard.

Default demo login:

```text
Email: demo@example.com
Password: password123
```

---

### 2. Dashboard

The dashboard shows an overview of parcel activity.

It displays:

- Total number of parcels
- Pending parcels
- In-transit parcels
- Delivered parcels
- Recent parcels
- Parcel tracking number
- Sender name
- Receiver name
- Receiver address
- Parcel weight
- Parcel status
- Parcel creation date
- Links to update parcel status
- Links to view invoice

The dashboard also includes a copy button for tracking numbers.

---

### 3. Send Parcel

Authenticated users can create a new parcel.

The send parcel form collects:

- Receiver full name
- Receiver phone number
- Receiver address
- Parcel weight
- Distance/pricing zone
- Optional parcel description

When a parcel is created, the system automatically generates a tracking number.

Tracking number format:

```text
SWP-XXXXXXXX
```

Example:

```text
SWP-A1B2C3D4
```

The tracking number is generated using:

```php
'SWP-' . strtoupper(substr(md5(uniqid()), 0, 8))
```

---

### 4. Pricing Zones

The system supports configurable delivery pricing zones.

Each pricing zone has:

- Zone name
- Base price
- Price per kg
- Description

Default zones from the SQL setup:

| Zone | Base Price | Price Per Kg | Description |
|---|---:|---:|---|
| Zone A - Local | ₦1,000 | ₦500 | Within same city |
| Zone B - Regional | ₦2,500 | ₦1,200 | Nearby states |
| Zone C - National | ₦5,000 | ₦2,000 | Across the country |
| Zone D - Remote | ₦8,000 | ₦3,500 | Hard to reach areas |

The pricing page allows users to:

- Add a new zone
- Edit an existing zone
- Delete a zone
- View current zones

---

### 5. Parcel Cost Calculation

Parcel delivery cost is calculated using this formula:

```text
Total Cost = Base Price + (Weight × Price Per Kg)
```

Example:

```text
Zone B:
Base Price = ₦2,500
Weight = 3kg
Price Per Kg = ₦1,200

Total Cost = ₦2,500 + (3 × ₦1,200)
Total Cost = ₦6,100
```

The send parcel page includes a live cost preview using JavaScript before the parcel is submitted.

---

### 6. Public Parcel Tracking

The application allows parcel tracking without login.

Public users can visit:

```text
track_parcel.php
```

They can enter a tracking number and view:

- Tracking number
- Sender name
- Receiver name
- Receiver address
- Parcel weight
- Current delivery status
- Delivery progress timeline

The public tracking page searches parcels by tracking number.

---

### 7. Parcel Status Management

Authenticated users can update parcel status.

Available statuses:

```text
Pending
Picked Up
In Transit
Out for Delivery
Delivered
Cancelled
```

The update status page loads the parcel by ID and allows the user to choose a new status from a dropdown.

---

### 8. Invoice Generation

Each parcel has an invoice page.

The invoice displays:

- Tracking number
- Payment status
- Invoice date
- Sender details
- Receiver details
- Parcel description
- Delivery zone
- Weight
- Rate per kg
- Base price
- Weight charge
- Total cost
- Delivery status

The invoice page also allows the user to mark an invoice as paid.

Payment statuses:

```text
Unpaid
Paid
```

---

### 9. Printable Invoice

The project includes a separate print invoice page.

This page is designed for printing or saving the invoice as a PDF.

It contains:

- Invoice heading
- Tracking number
- Date
- Payment status
- Sender and receiver details
- Pricing breakdown
- Total amount
- Print/save as PDF button

---

### 10. Logout

The logout page destroys the current PHP session and redirects the user back to the login page.

---

## Application File Structure

```text
Parcelyn/
├── SETUP_INSTRUCTIONS.md
├── dashboard.php
├── db.php
├── favicon.svg
├── index.php
├── invoice.php
├── logout.php
├── pricing.php
├── print_invoice.php
├── send_parcel.php
├── setup.php
├── setup.sql
├── track_parcel.php
└── update_status.php
```

---

## File-by-File Explanation

### `index.php`

This is the login page.

Responsibilities:

- Starts PHP session
- Loads database connection from `db.php`
- Accepts email and password from login form
- Searches for the user by email
- Verifies password using `password_verify()`
- Creates user session after successful login
- Redirects authenticated user to `dashboard.php`
- Displays error message for invalid login
- Provides link to public parcel tracking page

Important logic:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    header("Location: dashboard.php");
    exit;
}
```

---

### `db.php`

This file handles the database connection.

It uses PDO to connect to MySQL.

Default local database configuration:

```php
$host     = '127.0.0.1';
$dbname   = 'parcel_db';
$username = 'root';
$password = '';
$port     = '3306';
```

The connection uses:

```php
charset=utf8
```

PDO error mode is set to throw exceptions:

```php
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

Any file that needs database access includes this file using:

```php
require 'db.php';
```

---

### `dashboard.php`

This is the main authenticated dashboard.

Responsibilities:

- Ensures user is logged in
- Loads database connection
- Counts total parcels
- Counts pending parcels
- Counts in-transit parcels
- Counts delivered parcels
- Fetches the latest 20 parcels
- Displays parcel records in a table
- Provides navigation to:
  - Create new parcel
  - Track parcel
  - Pricing zones
  - Logout
  - Update status
  - View invoice

Dashboard statistics queries:

```php
$total     = $pdo->query("SELECT COUNT(*) FROM parcels")->fetchColumn();
$pending   = $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='Pending'")->fetchColumn();
$transit   = $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='In Transit'")->fetchColumn();
$delivered = $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='Delivered'")->fetchColumn();
```

Recent parcels query:

```php
$parcels = $pdo->query("SELECT * FROM parcels ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
```

The page also includes a helper function for displaying different colors for different parcel statuses.

---

### `send_parcel.php`

This page allows authenticated users to create parcels.

Responsibilities:

- Ensures user is logged in
- Loads all pricing zones
- Accepts parcel creation form data
- Generates a tracking number
- Calculates parcel delivery cost
- Inserts the new parcel into the database
- Displays success message with tracking number and cost
- Links to the generated invoice

Pricing zones are loaded using:

```php
$zones = $pdo->query("SELECT * FROM price_zones ORDER BY price_per_kg ASC")->fetchAll(PDO::FETCH_ASSOC);
```

Cost calculation:

```php
$cost = $zone['base_price'] + ($weight * $zone['price_per_kg']);
```

Parcel insert:

```php
$stmt = $pdo->prepare("INSERT INTO parcels
    (tracking_number, sender_id, sender_name, receiver_name, receiver_address,
     receiver_phone, weight, description, zone_id, cost)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
```

The form also contains JavaScript that previews the delivery cost before submission.

---

### `pricing.php`

This page manages pricing and distance zones.

Responsibilities:

- Ensures user is logged in
- Adds new pricing zones
- Updates existing pricing zones
- Deletes pricing zones
- Lists all pricing zones
- Shows the delivery cost formula

Add zone query:

```sql
INSERT INTO price_zones 
(zone_name, price_per_kg, base_price, description) 
VALUES (?,?,?,?)
```

Update zone query:

```sql
UPDATE price_zones 
SET zone_name=?, price_per_kg=?, base_price=?, description=? 
WHERE id=?
```

Delete zone query:

```sql
DELETE FROM price_zones WHERE id = ?
```

Important note:

Deleting a pricing zone that is already linked to parcels may fail if foreign key constraints are active and parcels still reference that zone.

---

### `track_parcel.php`

This is the public parcel tracking page.

Responsibilities:

- Allows tracking without login
- Accepts a tracking number through the query string
- Searches for a matching parcel
- Displays parcel information
- Displays a visual delivery progress timeline

Tracking search query:

```php
$stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ?");
$stmt->execute([strtoupper(trim($_GET['tracking']))]);
```

Delivery progress steps:

```php
$steps = ['Pending', 'Picked Up', 'In Transit', 'Out for Delivery', 'Delivered'];
```

The current step is calculated by matching the parcel status to the progress steps.

Note:

The `Cancelled` status exists in the database, but it is not part of the public tracking timeline steps.

---

### `update_status.php`

This page allows authenticated users to update the delivery status of a parcel.

Responsibilities:

- Ensures user is logged in
- Loads parcel by ID
- Redirects to dashboard if parcel does not exist
- Displays parcel tracking number, receiver, and address
- Allows status update through a dropdown
- Updates the parcel status in the database

Status update query:

```sql
UPDATE parcels SET status = ? WHERE id = ?
```

Available statuses:

```php
$statuses = ['Pending','Picked Up','In Transit','Out for Delivery','Delivered','Cancelled'];
```

---

### `invoice.php`

This page displays a full invoice for a parcel.

Responsibilities:

- Ensures user is logged in
- Loads parcel by ID
- Joins parcel with its pricing zone
- Displays invoice details
- Displays payment status
- Allows invoice to be marked as paid
- Links to printable invoice page

Invoice query:

```sql
SELECT p.*, z.zone_name, z.base_price, z.price_per_kg, z.description as zone_desc
FROM parcels p
LEFT JOIN price_zones z ON p.zone_id = z.id
WHERE p.id = ?
```

Mark as paid query:

```sql
UPDATE parcels SET payment_status='Paid' WHERE id=?
```

The invoice displays the cost breakdown:

```text
Base Price
Weight Charge
Total Cost
```

---

### `print_invoice.php`

This page provides a printer-friendly invoice.

Responsibilities:

- Loads parcel by ID
- Joins parcel with pricing zone
- Displays invoice in a printable format
- Provides a button to print or save as PDF
- Provides a close button

Unlike `invoice.php`, this file does not currently check whether the user is logged in.

This means anyone with a valid invoice ID URL could potentially view the printable invoice.

Recommended improvement:

```php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
```

---

### `logout.php`

This file logs the user out.

Responsibilities:

- Starts the current session
- Destroys the session
- Redirects to login page

Logic:

```php
session_start();
session_destroy();
header("Location: index.php");
exit;
```

---

### `setup.sql`

This is the recommended database setup file.

It creates:

- `parcel_db` database
- `users` table
- `price_zones` table
- `parcels` table
- Default pricing zones
- Demo admin user

The SQL file is the better setup option because it matches the current application features, including pricing zones, parcel cost, payment status, and zone relationships.

---

### `setup.php`

This is a PHP database setup script.

Important note:

This file appears to be outdated compared to `setup.sql`.

Issues:

- It creates a `parcels` table without `zone_id`, `cost`, and `payment_status`
- It does not create the `price_zones` table
- It contains hardcoded database credentials
- It appears configured for a hosted database rather than local development

Because the current app depends on `price_zones`, `zone_id`, `cost`, and `payment_status`, the recommended setup method is to use `setup.sql`, not `setup.php`.

Security warning:

Do not commit real database credentials into a public repository. Any exposed database password should be rotated immediately.

---

## Database Design

### Database Name

```text
parcel_db
```

---

## Tables

### `users`

Stores application users.

| Column | Type | Description |
|---|---|---|
| `id` | INT, Primary Key, Auto Increment | Unique user ID |
| `name` | VARCHAR(100) | User full name |
| `email` | VARCHAR(100), Unique | User email address |
| `password` | VARCHAR(255) | Hashed password |
| `role` | ENUM('admin', 'user') | User role |
| `created_at` | TIMESTAMP | Account creation time |

Default demo user:

```text
Name: Demo User
Email: demo@example.com
Role: admin
Password: password123
```

The password is stored as a hashed password.

---

### `price_zones`

Stores delivery pricing zones.

| Column | Type | Description |
|---|---|---|
| `id` | INT, Primary Key, Auto Increment | Unique zone ID |
| `zone_name` | VARCHAR(100) | Name of the zone |
| `price_per_kg` | DECIMAL(10,2) | Price charged per kg |
| `base_price` | DECIMAL(10,2) | Base delivery price |
| `description` | VARCHAR(255) | Zone description |
| `created_at` | TIMESTAMP | Zone creation time |

Default zones:

```text
Zone A - Local
Zone B - Regional
Zone C - National
Zone D - Remote
```

---

### `parcels`

Stores parcel delivery records.

| Column | Type | Description |
|---|---|---|
| `id` | INT, Primary Key, Auto Increment | Unique parcel ID |
| `tracking_number` | VARCHAR(50), Unique | Public tracking number |
| `sender_id` | INT | User who created the parcel |
| `sender_name` | VARCHAR(100) | Sender name copied from session |
| `receiver_name` | VARCHAR(100) | Receiver full name |
| `receiver_address` | TEXT | Receiver delivery address |
| `receiver_phone` | VARCHAR(20) | Receiver phone number |
| `weight` | DECIMAL(8,2) | Parcel weight in kg |
| `description` | TEXT | Parcel description |
| `zone_id` | INT | Linked pricing zone |
| `cost` | DECIMAL(10,2) | Calculated delivery cost |
| `payment_status` | ENUM('Unpaid','Paid') | Invoice payment status |
| `status` | ENUM(...) | Parcel delivery status |
| `created_at` | TIMESTAMP | Parcel creation time |
| `updated_at` | TIMESTAMP | Last update time |

Parcel status values:

```text
Pending
Picked Up
In Transit
Out for Delivery
Delivered
Cancelled
```

Payment status values:

```text
Unpaid
Paid
```

---

## Database Relationships

### User to Parcels

One user can create many parcels.

```text
users.id -> parcels.sender_id
```

Relationship:

```text
users 1 ---- many parcels
```

---

### Price Zone to Parcels

One pricing zone can be used by many parcels.

```text
price_zones.id -> parcels.zone_id
```

Relationship:

```text
price_zones 1 ---- many parcels
```

---

## Application Flow

## Authenticated User Flow

```text
Login
  ↓
Dashboard
  ↓
Create Parcel
  ↓
Select Pricing Zone
  ↓
System Calculates Cost
  ↓
System Generates Tracking Number
  ↓
Parcel Saved
  ↓
Invoice Generated
  ↓
Status Can Be Updated
  ↓
Parcel Can Be Tracked Publicly
```

---

## Public Tracking Flow

```text
Open Track Parcel Page
  ↓
Enter Tracking Number
  ↓
System Searches Parcel
  ↓
If Found: Display Parcel Details and Timeline
  ↓
If Not Found: Show Error Message
```

---

## Invoice Flow

```text
Parcel Created
  ↓
Invoice Available
  ↓
User Views Invoice
  ↓
User Can Print Invoice
  ↓
User Can Mark Invoice as Paid
```

---

## Setup Instructions

## Prerequisites

Before running the project, install:

- PHP 7.4 or higher
- MySQL or MariaDB
- XAMPP, MAMP, LAMP, WAMP, or similar local server package
- Web browser

---

## Step 1: Clone the Repository

```bash
git clone https://github.com/Victor-udenna/Parcelyn.git
cd Parcelyn
```

---

## Step 2: Start MySQL

If using XAMPP, start MySQL from the XAMPP control panel.

On macOS with XAMPP, you may use:

```bash
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
```

---

## Step 3: Create the Database

Recommended method:

```bash
mysql -u root -p < setup.sql
```

If your local MySQL root user has no password, use:

```bash
mysql -u root < setup.sql
```

Alternative using phpMyAdmin:

1. Open phpMyAdmin.
2. Create a database named:

```text
parcel_db
```

3. Open the SQL tab.
4. Paste the contents of `setup.sql`.
5. Run the SQL.

---

## Step 4: Configure Database Connection

Open:

```text
db.php
```

Update these values if needed:

```php
$host     = '127.0.0.1';
$dbname   = 'parcel_db';
$username = 'root';
$password = '';
$port     = '3306';
```

For default XAMPP local setup, these values are usually correct.

---

## Step 5: Start PHP Development Server

From the project root, run:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

---

## Step 6: Login

Use the demo account:

```text
Email: demo@example.com
Password: password123
```

---

## Main Pages / Routes

Because this is a simple PHP project, routes are direct PHP files.

| Page | Access | Purpose |
|---|---|---|
| `/index.php` | Public | Login page |
| `/dashboard.php` | Authenticated | Dashboard and parcel overview |
| `/send_parcel.php` | Authenticated | Create new parcel |
| `/pricing.php` | Authenticated | Manage pricing zones |
| `/track_parcel.php` | Public | Track parcel by tracking number |
| `/update_status.php?id={id}` | Authenticated | Update parcel status |
| `/invoice.php?id={id}` | Authenticated | View parcel invoice |
| `/print_invoice.php?id={id}` | Public currently | Printable invoice |
| `/logout.php` | Authenticated | Logout |

---

## Security Review

The project works for learning/demo purposes, but several security improvements are recommended before production use.

---

### 1. Remove Hardcoded Credentials

`setup.php` contains hardcoded database credentials.

This is unsafe for a public repository.

Recommended actions:

- Remove real credentials from the repository
- Rotate the exposed database password
- Use environment variables instead
- Add `.env` to `.gitignore`
- Use a sample config file like `.env.example`

Example safer config approach:

```php
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
```

---

### 2. Do Not Use `setup.php` in Production

`setup.php` appears outdated and does not match the current database structure required by the application.

Use:

```text
setup.sql
```

instead.

---

### 3. Protect `print_invoice.php`

`print_invoice.php` does not currently require login.

Recommended fix:

```php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
```

Without this, invoices may be accessible by guessing invoice IDs.

---

### 4. Add CSRF Protection

Forms currently submit directly without CSRF tokens.

Affected pages include:

- `send_parcel.php`
- `pricing.php`
- `update_status.php`
- `invoice.php?mark_paid=1`

Recommended improvement:

- Generate CSRF token in session
- Add hidden CSRF input to forms
- Validate token before processing POST actions

---

### 5. Validate User Input More Strictly

The application uses prepared statements in most database operations, which is good.

However, validation can be improved.

Recommended validation:

- Validate receiver phone number format
- Ensure weight is greater than zero
- Ensure selected zone exists before cost calculation
- Validate status against allowed status list
- Validate pricing zone values before insert/update
- Prevent empty or negative prices

---

### 6. Avoid GET Requests for Destructive Actions

Some actions are performed through GET requests:

```text
pricing.php?delete={id}
invoice.php?id={id}&mark_paid=1
```

Recommended improvement:

Use POST forms instead of GET for actions that change data.

---

### 7. Improve Role-Based Access Control

The database has a `role` column, but the current pages only check whether the user is logged in.

Recommended improvement:

- Allow only admins to manage pricing zones
- Allow only admins to update parcel status
- Allow users to view only their own parcels if multiple users are supported

Example:

```php
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
```

---

### 8. Hide Detailed Database Errors in Production

`db.php` currently displays connection error details.

Recommended production behavior:

- Log the detailed error privately
- Show a generic error to the user

Example:

```php
die("Database connection failed. Please contact support.");
```

---

## Known Issues / Limitations

### 1. No Registration Page

The project includes login but does not include a user registration page.

Users must be inserted through SQL or setup script.

---

### 2. `setup.php` Is Inconsistent With Current Schema

The application currently expects:

- `price_zones`
- `zone_id`
- `cost`
- `payment_status`

But `setup.php` does not fully create the current schema.

Use `setup.sql`.

---

### 3. No Pagination on Dashboard

The dashboard only loads the latest 20 parcels.

There is no pagination, filtering, or search.

---

### 4. No Parcel Deletion

The system allows creating and updating parcels, but there is no delete parcel feature.

---

### 5. No Customer Notification

There is no email, SMS, or WhatsApp notification when a parcel status changes.

---

### 6. Public Tracking Shows Receiver Address

The public tracking page displays the receiver address.

Depending on privacy requirements, this may expose sensitive delivery information.

Recommended improvement:

Only show limited tracking information publicly.

---

### 7. No Audit Trail

Status updates are overwritten directly on the parcel record.

The system does not store historical status changes.

Recommended improvement:

Create a `parcel_status_history` table.

---

## Recommended Future Improvements

### 1. Add Status History

Create a table like:

```sql
CREATE TABLE parcel_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parcel_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    changed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parcel_id) REFERENCES parcels(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);
```

This would allow the tracking page to show real historical movement instead of only a static progress timeline.

---

### 2. Add User Management

Add pages for:

- Creating users
- Editing users
- Disabling users
- Assigning roles

---

### 3. Add Search and Filters

Useful dashboard filters:

- Search by tracking number
- Filter by status
- Filter by payment status
- Filter by date range
- Filter by receiver name
- Filter by zone

---

### 4. Add Pagination

Instead of only showing 20 recent parcels, add pagination.

Example:

```text
?page=1
?page=2
?page=3
```

---

### 5. Add Payment Integration

The invoice currently has manual payment status.

Future improvement:

- Integrate Paystack, Flutterwave, Stripe, or another payment provider
- Automatically mark invoices as paid after successful payment

---

### 6. Add Email/SMS Notifications

Notify customers when:

- Parcel is created
- Parcel is picked up
- Parcel is in transit
- Parcel is out for delivery
- Parcel is delivered

---

### 7. Add Environment Configuration

Move sensitive configuration into environment variables.

Recommended files:

```text
.env
.env.example
```

Example `.env.example`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=parcel_db
DB_USER=root
DB_PASSWORD=
```

---

### 8. Improve Routing Structure

Currently every page is a separate PHP file.

For a larger version, the project could be reorganized into:

```text
public/
app/
config/
views/
controllers/
models/
```

Or migrated to a framework like Laravel.

---

## Suggested `.gitignore`

```gitignore
.env
*.log
.DS_Store
Thumbs.db
vendor/
node_modules/
```

---

## Deployment Notes

For production deployment:

1. Remove or secure `setup.php`.
2. Rotate any exposed database credentials.
3. Configure database credentials through environment variables.
4. Disable detailed error messages.
5. Protect invoice routes.
6. Use HTTPS.
7. Add CSRF protection.
8. Restrict admin-only actions by role.
9. Make sure database backups are enabled.
10. Make sure the database user has only required permissions.

---

## Summary

Parcelyn is a simple PHP/MySQL parcel delivery system.

It supports:

- Admin login
- Parcel creation
- Delivery pricing zones
- Automatic cost calculation
- Tracking number generation
- Public parcel tracking
- Delivery status updates
- Invoice generation
- Printable invoices
- Manual payment status update


