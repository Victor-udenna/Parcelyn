<?php
require 'db.php';
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT p.*, z.zone_name, z.base_price, z.price_per_kg
    FROM parcels p
    LEFT JOIN price_zones z ON p.zone_id = z.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) die("Invoice not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
        <title>Invoice <?= $p['tracking_number'] ?> - Parcelyn</title>
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
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; padding: 40px; color: var(--text-dark); max-width: 700px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; margin-bottom: 32px; border-bottom: 3px solid var(--electric); padding-bottom: 20px; }
        .header h1 { font-size: 1.2rem; color: var(--navy); }
        .header p { color: var(--text-muted); font-size: 11px; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }
        .meta h4 { font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { border-bottom: 2px solid var(--electric); padding: 10px; text-align: left; font-size: 11px; color: var(--text-dark); }
        td { padding: 12px 10px; border-bottom: 1px solid #e8efff; font-size: 11px; color: var(--text-dark); }
        .totals { margin-left: auto; width: 260px; }
        .totals .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 11px; color: var(--text-dark); }
        .totals .grand { display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 800; border-top: 2px solid var(--electric); padding-top: 10px; margin-top: 8px; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .paid {  color: #0e3f83; }
        .unpaid {  color: #b82834; }
        .footer { margin-top: 40px; text-align: center; color: var(--text-muted); font-size: 11px; border-top: 1px solid #e5efff; padding-top: 20px; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;">
    <button onclick="window.print()"
            style="padding:10px 24px;background:var(--electric);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:11px;">
            Save as PDF
        </button>
        <button onclick="window.close()"
            style="padding:10px 24px;color:var(--navy);border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:11px;margin-left:10px;"> Close</button>
</div>

<div class="header">
    <div>
        <h1>Parcelyn</h1>
        <p>Parcel Delivery Services</p>
    </div>
    <div style="text-align:right;">
        <p style="font-size:14px;font-weight:700;padding-bottom:10px;">INVOICE</p>
        <p><?= htmlspecialchars($p['tracking_number']) ?></p>
        <p style="margin-top:6px;"><?= date('d M Y', strtotime($p['created_at'])) ?></p>
        <span class="badge <?= $p['payment_status']==='Paid' ? 'paid' : 'unpaid' ?>" style="margin-top:6px;display:inline-block;">
            <?= $p['payment_status'] ?>
        </span>
    </div>
</div>

<div class="meta" style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
        <h4>From</h4>
        <p style="font-size:11px;font-weight:700;padding-bottom:10px;">
            <strong><?= htmlspecialchars($p['sender_name']) ?></strong></p>
    </div>
    <div>
        <h4>To</h4>
        <p style="font-size:11px;font-weight:700;padding-bottom:10px;">
            <strong><?= htmlspecialchars($p['receiver_name']) ?></strong></p>
        <p style="font-size:11px;font-weight:700;padding-bottom:10px;">
            <?= htmlspecialchars($p['receiver_phone']) ?></p>
        <p style="font-size:11px;font-weight:700;padding-bottom:10px;"
        ><?= htmlspecialchars($p['receiver_address']) ?></p>
    </div>
</div>

<table>
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

<div class="totals">
    <div class="row"><span>Base Price</span><span>₦<?= number_format($p['base_price'], 2) ?></span></div>
    <div class="row"><span>Weight Charge</span><span>₦<?= number_format($p['weight'] * $p['price_per_kg'], 2) ?></span></div>
    <div class="grand"><span>TOTAL</span><span>₦<?= number_format($p['cost'], 2) ?></span></div>
</div>

<div class="footer">
    Parcelyn &nbsp;|&nbsp; admin@delivery.com &nbsp;|&nbsp; Generated <?= date('d M Y H:i') ?>
</div>

<script>
    // Auto open print dialog
    window.onload = function() {
        // uncomment below to auto-print on open
        // window.print();
    }
</script>
</body>
</html>