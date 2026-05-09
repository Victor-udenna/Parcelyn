<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require 'db.php';

// Counts
$total     = $pdo->query("SELECT COUNT(*) FROM parcels")->fetchColumn();
$pending   = $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='Pending'")->fetchColumn();
$transit   = $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='In Transit'")->fetchColumn();
$delivered = $pdo->query("SELECT COUNT(*) FROM parcels WHERE status='Delivered'")->fetchColumn();

// Recent parcels
$parcels = $pdo->query("SELECT * FROM parcels ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

// Status badge colors
function statusColor($s) {
    return match($s) {
        'Pending'          => '#f39c12',
        'Picked Up'        => '#3498db',
        'In Transit'       => '#9b59b6',
        'Out for Delivery' => '#e67e22',
        'Delivered'        => '#27ae60',
        'Cancelled'        => '#e74c3c',
        default            => '#95a5a6'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Parcelyn</title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml" />
    <style>
        :root {
            --deep-navy: #ff0000;
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
        body { font-family: 'Segoe UI', sans-serif; background: var(--surface-soft); color: var(--surface); }
        nav {
            background: var(--navy); color: #fff;
            padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        nav h1 { font-size: 1.4rem; }
        nav a { color: var(--electric); text-decoration: none; margin-left: 20px; font-weight: 600; }
        .container { padding: 32px; max-width: 1200px; margin: 0 auto; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card {
            background: var(--surface); border-radius: 12px;
            padding: 24px; text-align: center;
            box-shadow: 0 16px 40px rgba(0,0,0,0.16);
            color: var(--text-dark);
        }
        .stat-card .num { font-size: 2.5rem; font-weight: 800; color: var(--navy); }
        .stat-card .label { color: var(--text-muted); font-size: 0.85rem; margin-top: 4px; }
        .actions { display: flex; gap: 12px; margin-bottom: 24px; }
        .btn {
            padding: 12px 24px; border-radius: 8px;
            text-decoration: none; font-weight: 700;
            font-size: 0.9rem; border: none; cursor: pointer;
        }
        .btn-primary { background: var(--electric); color: var(--surface); }
        .btn-secondary { background: var(--navy); color: var(--surface); }
        table { width: 100%; border-collapse: collapse; background: var(--surface); border-radius: 12px; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,0.16); }
        th { background: var(--navy); color: #fff;  padding: 14px 16px; text-align: left; font-size: 0.85rem; }
        td { padding: 13px 16px; border-bottom: 1px solid #e6ecff; cursor: pointer; font-size: 11px; color: var(--text-dark); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f0f4ff; }
        .tracking-cell { display: flex; align-items: center; gap: 10px; }
        .copy-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 26px; height: 26px; border-radius: 6px;
            background: var(--electric); color: #fff; border: none; cursor: pointer;
            font-size: 0.95rem; transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .copy-btn:hover { transform: scale(1.05); opacity: 0.92; }
        .badge {
            padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; color: #fff;
        }
        .edit-link { color: var(--electric); text-decoration: none; font-weight: 600; }
        .table-wrapper { overflow-x: auto; }
        @media (max-width: 1024px) {
            .container { padding: 24px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .actions { flex-wrap: wrap; }
            .btn { width: 100%; justify-content: center; }
        }
        @media (max-width: 720px) {
            nav { flex-wrap: wrap; gap: 12px; padding: 16px 20px; }
            nav div { width: 100%; display: flex; flex-wrap: wrap; gap: 12px; justify-content: flex-start; }
            .stats { grid-template-columns: 1fr; }
            .actions { flex-direction: column; }
            .table-wrapper { margin-bottom: 16px; }
            table { min-width: 720px; }
            th, td { padding: 12px 10px; font-size: 0.78rem; }
            .tracking-cell { gap: 6px; flex-wrap: wrap; }
        }
        @media (max-width: 520px) {
            .container { padding: 16px; }
        }
    </style>
</head>
<body>
<nav>
    <h1>Parcelyn</h1>
    <div>
        <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
<a href="pricing.php">Pricing</a>
<a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="stats">
        <div class="stat-card"><div class="num"><?= $total ?></div><div class="label">Total Parcels</div></div>
        <div class="stat-card"><div class="num" style="color:#f39c12"><?= $pending ?></div><div class="label">Pending</div></div>
        <div class="stat-card"><div class="num" style="color:#9b59b6"><?= $transit ?></div><div class="label">In Transit</div></div>
        <div class="stat-card"><div class="num" style="color:#27ae60"><?= $delivered ?></div><div class="label">Delivered</div></div>
    </div>

    <div class="actions">
        <a href="send_parcel.php" class="btn btn-primary">New Parcel</a>
        <a href="track_parcel.php" class="btn btn-secondary"> Track Parcel</a>
    </div>

    <div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Tracking #</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Address</th>
                <th>Weight (kg)</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($parcels as $p): ?>
            <tr data-href="invoice.php?id=<?= $p['id'] ?>">
                <td class="tracking-cell">
                    <strong><?= htmlspecialchars($p['tracking_number']) ?></strong>
                    <button type="button" class="copy-btn" data-track="<?= htmlspecialchars($p['tracking_number']) ?>" title="Copy tracking number"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard-minus" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M5.5 9.5A.5.5 0 0 1 6 9h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5"/>
  <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
  <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
</svg></button>
                </td>
                <td><?= htmlspecialchars($p['sender_name']) ?></td>
                <td><?= htmlspecialchars($p['receiver_name']) ?></td>
                <td><?= htmlspecialchars($p['receiver_address']) ?></td>
                <td><?= $p['weight'] ?></td>
                <td>
                    <span class="badge" style="background:<?= statusColor($p['status']) ?>">
                        <?= $p['status'] ?>
                    </span>
                </td>
                <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
<td style="display:flex;gap:8px;">
    <a href="update_status.php?id=<?= $p['id'] ?>" class="edit-link">Update</a>
    <a href="invoice.php?id=<?= $p['id'] ?>" class="edit-link" style="color:var(--electric);">Invoice</a>
</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>    </div></div>
<script>
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.addEventListener('click', () => {
            window.location.href = row.dataset.href;
        });
    });
    document.querySelectorAll('a, .copy-btn').forEach(el => {
        el.addEventListener('click', event => event.stopPropagation());
    });

    const copyIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M4 1.5A1.5 1.5 0 0 0 2.5 3v9A1.5 1.5 0 0 0 4 13.5h6.5A1.5 1.5 0 0 0 12 12V3A1.5 1.5 0 0 0 10.5 1.5H4zm0 1h6.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5H4a.5.5 0 0 1-.5-.5V3a.5.5 0 0 1 .5-.5z"/>
        <path d="M8.5 4a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 8.5 4zm0 3a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 8.5 7z"/>
    </svg>`;
    const successIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M16 2a2 2 0 0 1-2 2H6.5L5 6.5V14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2z" fill-opacity="0"/>
        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.992 4.992a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l1.94 1.94 3.646-4.296z"/>
    </svg>`;

    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const trackId = button.dataset.track;
            const originalHtml = button.innerHTML;
            try {
                await navigator.clipboard.writeText(trackId);
                button.innerHTML = successIcon;
                setTimeout(() => button.innerHTML = originalHtml, 1200);
            } catch (err) {
                console.error('Copy failed', err);
            }
        });
    });
</script>
</body>
</html>