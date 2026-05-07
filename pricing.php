<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require 'db.php';

$success = $error = '';

// Add new zone
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_zone'])) {
    $stmt = $pdo->prepare("INSERT INTO price_zones (zone_name, price_per_kg, base_price, description) VALUES (?,?,?,?)");
    try {
        $stmt->execute([
            $_POST['zone_name'],
            $_POST['price_per_kg'],
            $_POST['base_price'],
            $_POST['description']
        ]);
        $success = "Zone added successfully!";
    } catch (Exception $e) {
        $error = "Error adding zone.";
    }
}

// Delete zone
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM price_zones WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: pricing.php");
    exit;
}

// Update zone
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_zone'])) {
    $pdo->prepare("UPDATE price_zones SET zone_name=?, price_per_kg=?, base_price=?, description=? WHERE id=?")
        ->execute([
            $_POST['zone_name'],
            $_POST['price_per_kg'],
            $_POST['base_price'],
            $_POST['description'],
            $_POST['zone_id']
        ]);
    $success = "Zone updated!";
}

$zones = $pdo->query("SELECT * FROM price_zones ORDER BY price_per_kg ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pricing Zones - Parcelyn</title>
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
        .container { padding: 32px; max-width: 1100px; margin: 0 auto; }
        h2 { color: var(--text-dark); margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        .card { background: var(--surface); padding: 28px; border-radius: 12px; box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-dark); font-size: 0.85rem; }
        input, textarea {
            width: 100%; padding: 11px; border: 2px solid var(--border-soft);
            border-radius: 8px; font-size: 0.95rem; margin-bottom: 16px;
            font-family: inherit; transition: border 0.3s; background: var(--surface);
            color: var(--text-dark);
        }
        input:focus, textarea:focus { outline: none; border-color: var(--electric); }
        button, .btn {
            padding: 11px 20px; border-radius: 8px;
            font-weight: 700; font-size: 0.9rem; cursor: pointer; border: none;
            text-decoration: none; display: inline-block;
        }
        .btn-primary { background: var(--electric); color: var(--surface); width: 100%; text-align: center; }
        .btn-danger { background: #ff6b6b; color: #fff; font-size: 0.8rem; padding: 6px 12px; }
        .btn-edit { background: var(--navy); color: var(--surface); font-size: 0.8rem; padding: 6px 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--navy); color: #fff; padding: 12px 14px; text-align: left; font-size: 0.85rem; }
        td { padding: 12px 14px; border-bottom: 1px solid #e6ecff; font-size: 11px; color: var(--text-dark); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f0f4ff; }
        .success { background: #eaf4ff; color: #0c3d72; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #ffe8e8; color: #a22f36; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .formula { background: #fff; border-left: 4px solid var(--electric); padding: 14px;  margin-bottom: 20px; font-size: 0.85rem; color: var(--text-dark); }
        .formula strong { color: var(--navy); }
        /* Modal */
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999; justify-content:center; align-items:center; }
        .modal.active { display:flex; }
        .modal-card { background: var(--surface); padding:28px; border-radius:12px; width:100%; max-width:420px; }
        .modal-card h3 { margin-bottom:20px; color: var(--text-dark); }
        .modal-actions { display:flex; gap:12px; }
        .btn-cancel { background:#d4d9e8; color: var(--text-dark); flex:1; }
        .btn-save { background: var(--electric); color: var(--surface); flex:1; }
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
    <h2>Pricing & Distance Zones</h2>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <div class="formula">
        <strong>Cost Formula:</strong> &nbsp;
        Total Cost = <strong>Base Price</strong> + (<strong>Weight (kg)</strong> × <strong>Price per kg</strong>)
        <br><br>
        <em>Example: Zone B (Base ₦2,500 + 3kg × ₦1,200) = <strong>₦6,100</strong></em>
    </div>

    <div class="grid">
        <!-- Add Zone Form -->
        <div class="card">
            <h3 style="margin-bottom:20px;color:var(--text-dark);">Add New Zone</h3>
            <form method="POST">
                <label>Zone Name</label>
                <input type="text" name="zone_name" placeholder="e.g. Zone A - Local" required>

                <label>Base Price (₦)</label>
                <input type="number" name="base_price" step="0.01" placeholder="1000" required>

                <label>Price per kg (₦)</label>
                <input type="number" name="price_per_kg" step="0.01" placeholder="500" required>

                <label>Description</label>
                <input type="text" name="description" placeholder="e.g. Within same city">

                <button type="submit" name="add_zone" class="btn btn-primary">+ Add Zone</button>
            </form>
        </div>

        <!-- Zones Table -->
        <div class="card">
            <h3 style="margin-bottom:20px;color:var(--text-dark);">Current Zones</h3>
            <table>
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Base (₦)</th>
                        <th>Per kg (₦)</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($zones as $z): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($z['zone_name']) ?></strong></td>
                        <td>₦<?= number_format($z['base_price'], 2) ?></td>
                        <td>₦<?= number_format($z['price_per_kg'], 2) ?></td>
                        <td><?= htmlspecialchars($z['description']) ?></td>
                        <td style="display:flex;gap:6px;">
                            <button class="btn btn-edit"
                                onclick="openEdit(<?= $z['id'] ?>, '<?= addslashes($z['zone_name']) ?>', <?= $z['base_price'] ?>, <?= $z['price_per_kg'] ?>, '<?= addslashes($z['description']) ?>')">
                                Edit
                            </button>
                            <a href="pricing.php?delete=<?= $z['id'] ?>"
                               onclick="return confirm('Delete this zone?')"
                               class="btn btn-danger">Del</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-card">
        <h3>Edit Zone</h3>
        <form method="POST">
            <input type="hidden" name="zone_id" id="edit_id">
            <label>Zone Name</label>
            <input type="text" name="zone_name" id="edit_name" required>
            <label>Base Price (₦)</label>
            <input type="number" name="base_price" id="edit_base" step="0.01" required>
            <label>Price per kg (₦)</label>
            <input type="number" name="price_per_kg" id="edit_pkg" step="0.01" required>
            <label>Description</label>
            <input type="text" name="description" id="edit_desc">
            <div class="modal-actions" style="margin-top:8px;">
                <button type="button" class="btn btn-cancel" onclick="closeEdit()">Cancel</button>
                <button type="submit" name="update_zone" class="btn btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, base, pkg, desc) {
    document.getElementById('edit_id').value   = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_base').value = base;
    document.getElementById('edit_pkg').value  = pkg;
    document.getElementById('edit_desc').value = desc;
    document.getElementById('editModal').classList.add('active');
}
function closeEdit() {
    document.getElementById('editModal').classList.remove('active');
}
</script>
</body>
</html>