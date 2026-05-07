<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require 'db.php';

$id     = (int)$_GET['id'];
$stmt   = $pdo->prepare("SELECT * FROM parcels WHERE id = ?");
$stmt->execute([$id]);
$parcel = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$parcel) { header("Location: dashboard.php"); exit; }

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE parcels SET status = ? WHERE id = ?")
        ->execute([$_POST['status'], $id]);
    $success = "Status updated successfully!";
    $parcel['status'] = $_POST['status'];
}

$statuses = ['Pending','Picked Up','In Transit','Out for Delivery','Delivered','Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Status - Parcelyn</title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml" />
    <style>
        :root {
            --deep-navy: #081830;
            --navy: #0f234e;
            --electric: #2e8dff;
            --electric-strong: #1f6fe4;
            --surface: #ffffff;
            --surface-soft: #eef4ff;
            --text-dark: #122750;
            --text-muted: #6b82a8;
            --border-soft: rgba(46,141,255,0.15);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--surface-soft); color: var(--text-dark); }
        nav { background: var(--navy); color: #fff; padding: 16px 32px; display: flex; justify-content: space-between; }
        nav h1 { font-size: 1.4rem; }
        nav a { color: var(--electric); text-decoration: none; font-weight: 600; }
        .container { padding: 32px; max-width: 500px; margin: 0 auto; }
        h2 { margin-bottom: 24px; color: var(--text-dark); }
        .card { background: var(--surface); padding: 32px; border-radius: 12px; box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
        .meta { background: #eef4ff; padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .meta p { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 6px; }
        .meta strong { color: var(--text-dark); }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--text-dark); }
        select {
            width: 100%; padding: 12px; border: 2px solid var(--border-soft);
            border-radius: 8px; font-size: 1rem; margin-bottom: 20px;
            background: var(--surface); color: var(--text-dark); cursor: pointer;
        }
        select:focus { outline: none; border-color: var(--electric); }
        button {
            width: 100%; padding: 14px; background: var(--electric); color: var(--surface);
            border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer;
        }
        .success { background: #eaf4ff; color: #0c3d72; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .back { display: inline-block; margin-bottom: 20px; color: var(--electric); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<nav>
    <h1>Parcelyn</h1>
    <a href="dashboard.php">← Dashboard</a>
</nav>

<div class="container">
    <a href="dashboard.php" class="back">← Back</a>
    <h2>Update Parcel Status</h2>
    <div class="card">
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <div class="meta">
            <p><strong>Tracking:</strong> <?= htmlspecialchars($parcel['tracking_number']) ?></p>
            <p><strong>Receiver:</strong> <?= htmlspecialchars($parcel['receiver_name']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($parcel['receiver_address']) ?></p>
        </div>

        <form method="POST">
            <label>Current Status</label>
            <select name="status">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $parcel['status'] === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Update Status</button>
        </form>
    </div>
</div>
</body>
</html>