<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parcelyn - Login</title>
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
        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--deep-navy);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; color: var(--surface);
        }
        .card {
            background: var(--surface);
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 24px 65px rgba(0,0,0,0.25);
        }
        h2 { color: var(--text-dark); margin-bottom: 6px; font-size: 1.8rem; }
        p.sub { color: var(--text-muted); margin-bottom: 24px; font-size: 0.9rem; }
        label { display: block; margin-bottom: 6px; color: var(--text-dark); font-weight: 600; font-size: 0.85rem; }
        input {
            width: 100%; padding: 12px;
            border: 2px solid rgba(46,141,255,0.18); border-radius: 8px;
            font-size: 1rem; margin-bottom: 18px; transition: border 0.3s;
            color: var(--text-dark);
            background: #fff;
        }
        input:focus { outline: none; border-color: var(--electric); }
        button {
            width: 100%; padding: 13px;
            background: var(--electric); color: var(--surface);
            border: none; border-radius: 8px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background: var(--electric-strong); }
        .error { background: #ffe8e8; color: #a22f36; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem; }
        .track-link { text-align: center; margin-top: 16px; }
        .track-link a { color: var(--electric); text-decoration: none; font-size: 0.9rem; }
        @media (max-width: 480px) {
            body { padding: 16px; }
            .card { padding: 26px; }
            h2 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Parcelyn</h2>
    <p class="sub">Login to manage deliveries</p>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <button type="submit">Login</button>
    </form>

    <div class="track-link">
        <a href="track_parcel.php">🔍 Track a parcel without login</a>
    </div>
</div>
</body>
</html>