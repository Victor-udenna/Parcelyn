<?php
require 'db.php';
$parcel = null;
$error  = '';

if (isset($_GET['tracking']) && !empty($_GET['tracking'])) {
    $stmt = $pdo->prepare("SELECT * FROM parcels WHERE tracking_number = ?");
    $stmt->execute([strtoupper(trim($_GET['tracking']))]);
    $parcel = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$parcel) $error = "No parcel found with that tracking number.";
}

$steps = ['Pending', 'Picked Up', 'In Transit', 'Out for Delivery', 'Delivered'];
function stepIndex($status) {
    global $steps;
    $idx = array_search($status, $steps);
    return $idx !== false ? $idx : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Parcel - Parcelyn</title>
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
        body { font-family: 'Segoe UI', sans-serif; background: var(--deep-navy); min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 40px 16px; color: var(--surface); }
        h1 { color: var(--surface); font-size: 2rem; margin-bottom: 8px; }
        .sub { color: #aac4ff; margin-bottom: 32px; }
        .search-box { display: flex; gap: 12px; width: 100%; max-width: 500px; margin-bottom: 32px; }
        input {
            flex: 1; padding: 14px; border: none; border-radius: 8px;
            font-size: 1rem; outline: none; background: var(--surface); color: var(--text-dark);
        }
        button {
            padding: 14px 24px; background: var(--electric); color: var(--surface);
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 1rem;
        }
        .card {
            background: var(--surface); border-radius: 12px; padding: 32px;
            width: 100%; max-width: 600px; box-shadow: 0 24px 65px rgba(0,0,0,0.24);
        }
        .card h2 { color: var(--text-dark); margin-bottom: 6px; }
        .tracking-num { color: var(--electric); font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 28px; }
        .info-item label { font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; }
        .info-item p { color: var(--text-dark); font-weight: 600; margin-top: 2px; }
        .timeline { position: relative; padding-left: 24px; }
        .timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: rgba(255,255,255,0.15); }
        .step { position: relative; margin-bottom: 20px; }
        .step .dot {
            position: absolute; left: -20px; top: 3px;
            width: 14px; height: 14px; border-radius: 50%;
            background: #dbe8ff; border: 2px solid #fff;
        }
        .step.done .dot { background: var(--electric); }
        .step.current .dot { background: var(--electric-strong); box-shadow: 0 0 0 4px rgba(46,141,255,0.2); }
        .step .name { font-weight: 700; color: #aac4ff; font-size: 0.9rem; }
        .step.done .name, .step.current .name { color: var(--text-dark); }
        .error { background: #ffe8e8; color: #a22f36; padding: 12px 20px; border-radius: 8px; width: 100%; max-width: 500px; }
        .login-link { color: var(--electric); text-decoration: none; margin-top: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>Parcelyn</h1>
    <p class="sub">Real-time parcel tracking</p>

    <form method="GET" style="width:100%;max-width:500px;">
        <div class="search-box">
            <input type="text" name="tracking" placeholder="Enter tracking number e.g. SWP-ABC12345"
                   value="<?= htmlspecialchars($_GET['tracking'] ?? '') ?>">
            <button type="submit">Track</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($parcel): ?>
    <div class="card">
        <h2><?= htmlspecialchars($parcel['receiver_name']) ?>'s Parcel</h2>
        <div class="tracking-num"><?= htmlspecialchars($parcel['tracking_number']) ?></div>

        <div class="info-grid">
            <div class="info-item">
                <label>From</label>
                <p><?= htmlspecialchars($parcel['sender_name']) ?></p>
            </div>
            <div class="info-item">
                <label>To</label>
                <p><?= htmlspecialchars($parcel['receiver_name']) ?></p>
            </div>
            <div class="info-item">
                <label>Address</label>
                <p><?= htmlspecialchars($parcel['receiver_address']) ?></p>
            </div>
            <div class="info-item">
                <label>Weight</label>
                <p><?= $parcel['weight'] ?> kg</p>
            </div>
        </div>

        <h3 style="margin-bottom:16px;color:var(--text-dark);">Delivery Progress</h3>
        <div class="timeline">
            <?php
            $current = stepIndex($parcel['status']);
            foreach ($steps as $i => $step):
                $class = $i < $current ? 'done' : ($i === $current ? 'current' : '');
            ?>
            <div class="step <?= $class ?>">
                <div class="dot"></div>
                <div class="name"><?= $step ?><?= $i === $current ? ' ← Current' : '' ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <a href="index.php" class="login-link">Admin Login →</a>
</body>
</html>