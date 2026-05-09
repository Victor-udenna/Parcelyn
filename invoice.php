<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require 'db.php';

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT p.*, z.zone_name, z.base_price, z.price_per_kg, z.description as zone_desc
    FROM parcels p
    LEFT JOIN price_zones z ON p.zone_id = z.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { header("Location: dashboard.php"); exit; }

// Mark as paid
if (isset($_GET['mark_paid'])) {
    $pdo->prepare("UPDATE parcels SET payment_status='Paid' WHERE id=?")->execute([$id]);
    header("Location: invoice.php?id=$id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $p['tracking_number'] ?> - Parcelyn</title>
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
        nav h1 { font-size: 11px; }
        nav a { color: var(--electric); text-decoration: none; font-weight: 600; margin-left: 16px; }
        .container { padding: 32px; max-width: 720px; margin: 0 auto; }
        .actions { display: flex; gap: 12px; margin-bottom: 24px; }
        .btn { padding: 11px 22px; border-radius: 8px; font-weight: 700; font-size: 11px; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: var(--electric); color: #fff; }
        .btn-dark { background: var(--navy); color: #fff; }
        .btn-green { background: #0c3d72; color: #fff; }

        /* Invoice Card */
        .invoice { background: var(--surface); border-radius: 12px; padding: 48px; box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
        .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .inv-header h2 { font-size: 1.1rem; color: var(--text-dark); }
        .inv-header p { color: var(--text-muted); font-size: 11px; margin-top: 4px; }
        .inv-badge { padding: 6px 16px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-paid {  color: #0e3f83; }
        .badge-unpaid {  color: #b82430; }

        .inv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 36px; padding-bottom: 32px; border-bottom: 2px solid #dce7ff; }
        .inv-meta h4 { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .inv-meta p { color: var(--text-dark); font-size: 0.95rem; margin-bottom: 4px; }

        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .inv-table th { background: #eef4ff; color: var(--text-dark); font-size: 11px; text-transform: uppercase; padding: 12px 16px; text-align: left; }
        .inv-table td { padding: 14px 16px; border-bottom: 1px solid #e8efff; color: var(--text-dark); font-size: 11px; }

        .inv-totals { margin-left: auto; width: 280px; }
        .inv-totals .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 11px; color: var(--text-muted); }
        .inv-totals .total-row { display: flex; justify-content: space-between; padding: 14px 0; font-size: 1rem; font-weight: 800; color: var(--text-dark); border-top: 2px solid var(--electric); margin-top: 8px; }

        .inv-footer { margin-top: 40px; padding-top: 24px; border-top: 1px solid #dce7ff; text-align: center; color: var(--text-muted); font-size: 10px; }
        @media (max-width: 840px) {
            .container { padding: 24px; }
            .inv-header { flex-direction: column; gap: 20px; }
            .inv-meta { grid-template-columns: 1fr; }
            .inv-table th, .inv-table td { font-size: 10px; padding: 10px 12px; }
            .inv-totals { width: 100%; margin-left: 0; }
        }
        @media (max-width: 520px) {
            .actions { flex-direction: column; gap: 12px; }
            .invoice { padding: 24px; }
            .inv-header h2 { font-size: 1rem; }
            .inv-footer { font-size: 9px; }
        }
    </style>
</head>
<body>
<nav>
    <h1>Parcelyn</h1>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="actions">
        <a href="dashboard.php" class="btn btn-dark">←</a>
        <a href="print_invoice.php?id=<?= $id ?>" target="_blank" class="btn btn-primary">Print</a>
       <?php if (($p['payment_status'] ?? 'Unpaid') === 'Unpaid'): ?>
            <a href="invoice.php?id=<?= $id ?>&mark_paid=1" class="btn btn-green"
               onclick="return confirm('Mark this parcel as Paid?')">Mark as Paid</a>
        <?php endif; ?>
    </div>

    <div class="invoice">
        <!-- Header -->
        <div class="inv-header">
            <div>
                <h2>Parcelyn</h2>
                <p>INVOICE</p>
                <p style="margin-top:8px;font-size:11px;font-weight:700;color:var(--text-dark);">
                    #<?= htmlspecialchars($p['tracking_number']) ?>
                </p>
            </div>
            <div style="text-align:right;">
                <?php $payStatus = $p['payment_status'] ?? 'Unpaid'; ?>
                <span class="inv-badge <?= $payStatus === 'Paid' ? 'badge-paid' : 'badge-unpaid' ?>">
                    <?= htmlspecialchars($payStatus) ?>
                </span>
                <p style="margin-top:12px;color:#888;font-size:11px;">
                    Date: <?= date('d M Y', strtotime($p['created_at'])) ?>
                </p>
            </div>
        </div>

        <!-- Sender / Receiver -->
        <div class="inv-meta" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4>From (Sender)</h4>
                    <p style="font-size:11px;font-weight:700;padding-bottom:10px;">
            <strong><?= htmlspecialchars($p['sender_name']) ?></strong></p>
            </div>
            <div>
                <h4>To (Receiver)</h4>
                <p style="font-size:11px;font-weight:700;padding-bottom:10px;">
            <strong><?= htmlspecialchars($p['receiver_name']) ?></strong></p>
        <p style="font-size:11px;font-weight:700;padding-bottom:10px;">
            <?= htmlspecialchars($p['receiver_phone']) ?></p>
        <p style="font-size:11px;font-weight:700;padding-bottom:10px;"
        ><?= htmlspecialchars($p['receiver_address']) ?></p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Zone</th>
                    <th>Weight</th>
                    <th>Rate/kg</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($p['description'] ?: 'Parcel Delivery') ?></td>
                    <td><?= htmlspecialchars($p['zone_name']) ?></td>
                    <td><?= $p['weight'] ?> kg</td>
                    <td>₦<?= number_format($p['price_per_kg'], 2) ?></td>
                    <td>₦<?= number_format($p['weight'] * $p['price_per_kg'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="inv-totals">
            <div class="row"><span>Base Price (<?= htmlspecialchars($p['zone_name']) ?>)</span><span>₦<?= number_format($p['base_price'], 2) ?></span></div>
            <div class="row"><span>Weight Charge</span><span>₦<?= number_format($p['weight'] * $p['price_per_kg'], 2) ?></span></div>
            <div class="total-row"><span>TOTAL</span><span>₦<?= number_format($p['cost'], 2) ?></span></div>
        </div>

        <!-- Status -->
        <div style="margin-top:28px;background:#eef4ff;padding:16px;border-radius:8px;font-size:0.9rem;">
            <strong>Delivery Status:</strong>
            <span style="margin-left:8px;color:var(--electric);font-weight:700;"><?= $p['status'] ?></span>
        </div>

        <div class="inv-footer">
            Thank you for using Parcelyn! &nbsp;|&nbsp; For support contact admin@delivery.com
        </div>
    </div>
</div>
</body>
</html>