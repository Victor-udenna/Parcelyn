<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require 'db.php';

$success = $error = '';
$zones = $pdo->query("SELECT * FROM price_zones ORDER BY price_per_kg ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking = 'SWP-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $weight   = (float)$_POST['weight'];
    $zone_id  = (int)$_POST['zone_id'];

    // Get zone pricing
    $zstmt = $pdo->prepare("SELECT * FROM price_zones WHERE id = ?");
    $zstmt->execute([$zone_id]);
    $zone = $zstmt->fetch(PDO::FETCH_ASSOC);

    // Calculate cost: Base + (weight × price_per_kg)
    $cost = $zone['base_price'] + ($weight * $zone['price_per_kg']);

    $stmt = $pdo->prepare("INSERT INTO parcels
        (tracking_number, sender_id, sender_name, receiver_name, receiver_address,
         receiver_phone, weight, description, zone_id, cost)
        VALUES (?,?,?,?,?,?,?,?,?,?)");

    try {
        $stmt->execute([
            $tracking,
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_POST['receiver_name'],
            $_POST['receiver_address'],
            $_POST['receiver_phone'],
            $weight,
            $_POST['description'],
            $zone_id,
            $cost
        ]);
        $new_id  = $pdo->lastInsertId();
        $success = "Parcel created! Tracking: <strong>$tracking</strong> | Cost: <strong>₦" . number_format($cost, 2) . "</strong> &nbsp; <a href='invoice.php?id=$new_id' style='color:var(--electric);'>View Invoice →</a>";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Parcel - Parcelyn</title>
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
        nav { background: var(--navy); color: #fff; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { font-size: 1.4rem; }
        nav a { color: var(--electric); text-decoration: none; font-weight: 600; margin-left: 16px; }
        .container { padding: 32px; max-width: 650px; margin: 0 auto; }
        h2 { margin-bottom: 24px; color: var(--text-dark); }
        .card { background: var(--surface); padding: 32px; border-radius: 12px; box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
        label { display: block; margin-bottom: 6px; color: var(--text-dark); font-weight: 600; font-size: 0.85rem; }
        input, select, textarea {
            width: 100%; padding: 12px; border: 2px solid var(--border-soft);
            border-radius: 8px; font-size: 1rem; margin-bottom: 18px;
            font-family: inherit; transition: border 0.3s; background: var(--surface);
            color: var(--text-dark);
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--electric); }
        textarea { height: 80px; resize: vertical; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        button { width: 100%; padding: 14px; background: var(--electric); color: var(--surface); border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; }
        button:hover { background: var(--electric-strong); }
        .success { background: #eaf4ff; color: #0c3d72; padding: 14px; border-radius: 8px; margin-bottom: 20px; line-height: 1.6; }
        .error { background: #ffe8e8; color: #a22f36; padding: 12px; border-radius: 8px; margin-bottom: 20px; }

        /* Cost Preview Box */
        .cost-preview { background: var(--navy); color: var(--surface); border-radius: 10px; padding: 20px; margin-bottom: 20px; display: none; }
        .cost-preview h3 { font-size: 0.85rem; color: #a8bddf; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .cost-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: var(--text-dark); }
        .cost-total { border-top: 1px solid rgba(17,57,117,0.2); margin-top: 10px; padding-top: 10px; font-size: 1.2rem; font-weight: 800; color: var(--electric); display: flex; justify-content: space-between; }
        .zone-info { font-size: 0.8rem; color: var(--text-muted); margin-top: 8px; }
        @media (max-width: 900px) {
            .container { padding: 24px; }
            .card { padding: 24px; }
            .row { grid-template-columns: 1fr; }
        }
        @media (max-width: 520px) {
            nav { flex-wrap: wrap; gap: 10px; }
            nav div { width: 100%; display: flex; flex-wrap: wrap; gap: 10px; }
            .container { padding: 16px; }
        }
    </style>
</head>
<body>
<nav>
    <h1>Parcelyn</h1>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="pricing.php">Pricing Zones</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Send New Parcel</h2>
    <div class="card">
        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <form method="POST">
            <div class="row">
                <div>
                    <label>Receiver's Full Name</label>
                    <input type="text" name="receiver_name" placeholder="John Doe" required>
                </div>
                <div>
                    <label>Receiver's Phone</label>
                    <input type="text" name="receiver_phone" placeholder="+234..." required>
                </div>
            </div>

            <label>Receiver's Address</label>
            <textarea name="receiver_address" placeholder="Full delivery address..." required></textarea>

            <div class="row">
                <div>
                    <label>Weight (kg)</label>
                    <input type="number" name="weight" id="weight" step="0.01" min="0.01"
                           placeholder="0.00" required oninput="calculateCost()">
                </div>
                <div>
                    <label>Distance Zone</label>
                    <select name="zone_id" id="zone_id" required onchange="calculateCost()">
                        <option value="">-- Select Zone --</option>
                        <?php foreach ($zones as $z): ?>
                            <option value="<?= $z['id'] ?>"
                                    data-base="<?= $z['base_price'] ?>"
                                    data-pkg="<?= $z['price_per_kg'] ?>"
                                    data-desc="<?= htmlspecialchars($z['description']) ?>">
                                <?= htmlspecialchars($z['zone_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Live Cost Preview -->
            <div class="cost-preview" id="costPreview">
                <h3>Cost Breakdown</h3>
                <div class="cost-row"><span>Base Price</span><span id="basePrice">₦0.00</span></div>
                <div class="cost-row"><span>Weight Charge (<span id="weightDisplay">0</span>kg × <span id="rateDisplay">₦0</span>)</span><span id="weightCost">₦0.00</span></div>
                <div class="cost-total"><span>Total Cost</span><span id="totalCost">₦0.00</span></div>
                <div class="zone-info" id="zoneDesc"></div>
            </div>

            <label>Description (optional)</label>
            <input type="text" name="description" placeholder="Electronics, Clothing, Documents...">

            <button type="submit">Create Parcel & Generate Invoice</button>
        </form>
    </div>
</div>

<script>
function calculateCost() {
    const weight  = parseFloat(document.getElementById('weight').value) || 0;
    const sel     = document.getElementById('zone_id');
    const opt     = sel.options[sel.selectedIndex];

    if (!sel.value || weight <= 0) {
        document.getElementById('costPreview').style.display = 'none';
        return;
    }

    const base    = parseFloat(opt.dataset.base);
    const pkg     = parseFloat(opt.dataset.pkg);
    const wCost   = weight * pkg;
    const total   = base + wCost;
    const desc    = opt.dataset.desc;

    document.getElementById('basePrice').textContent    = '₦' + base.toLocaleString('en-NG', {minimumFractionDigits:2});
    document.getElementById('weightDisplay').textContent = weight;
    document.getElementById('rateDisplay').textContent  = '₦' + pkg.toLocaleString('en-NG', {minimumFractionDigits:2});
    document.getElementById('weightCost').textContent   = '₦' + wCost.toLocaleString('en-NG', {minimumFractionDigits:2});
    document.getElementById('totalCost').textContent    = '₦' + total.toLocaleString('en-NG', {minimumFractionDigits:2});
    document.getElementById('zoneDesc').textContent     = '📍 ' + desc;
    document.getElementById('costPreview').style.display = 'block';
}
</script>
</body>
</html>