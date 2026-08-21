<?php
/**
 * Trackzo installer — run ONCE after uploading and setting config.php.
 * Visit  https://yourdomain.com/install.php  in a browser.
 * It creates all (empty) tables. No sample data — you create your own
 * account and records inside the app. Safe to re-run.
 */
require_once __DIR__ . '/inc/helpers.php';

$pdo = db();
$log = [];
$err = null;

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(160) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(80) DEFAULT 'Member',
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // migration for databases created before last_login existed
    try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME NULL"); } catch (Throwable $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        company VARCHAR(160),
        email VARCHAR(160),
        phone VARCHAR(60),
        address VARCHAR(200),
        city VARCHAR(120),
        status ENUM('active','inactive') DEFAULT 'active',
        joined_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        client_id INT NULL,
        site_address VARCHAR(220),
        type VARCHAR(120),
        status ENUM('planning','active','on-hold','completed') DEFAULT 'planning',
        budget DECIMAL(15,2) DEFAULT 0,
        spent DECIMAL(15,2) DEFAULT 0,
        progress INT DEFAULT 0,
        start_date DATE NULL,
        end_date DATE NULL,
        area INT DEFAULT 0,
        floors INT DEFAULT 0,
        manager VARCHAR(120),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        category VARCHAR(120),
        unit VARCHAR(80),
        stock DECIMAL(12,2) DEFAULT 0,
        min_stock DECIMAL(12,2) DEFAULT 0,
        rate DECIMAL(12,2) DEFAULT 0,
        supplier VARCHAR(160),
        last_updated DATE NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        supplier VARCHAR(160) NOT NULL,
        item VARCHAR(200),
        qty DECIMAL(12,2) DEFAULT 0,
        rate DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(15,2) DEFAULT 0,
        status ENUM('pending','approved','delivered','cancelled') DEFAULT 'pending',
        order_date DATE NULL,
        expected_date DATE NULL,
        project_id INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        txn_date DATE NULL,
        description VARCHAR(220) NOT NULL,
        category VARCHAR(120),
        type ENUM('income','expense') DEFAULT 'expense',
        amount DECIMAL(15,2) DEFAULT 0,
        status ENUM('paid','pending','overdue') DEFAULT 'paid',
        project_id INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        type ENUM('bank','cash','credit') DEFAULT 'bank',
        balance DECIMAL(15,2) DEFAULT 0,
        currency VARCHAR(10) DEFAULT 'INR',
        last_transaction DATE NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS calendar_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        event_date DATE NOT NULL,
        type ENUM('meeting','deadline','inspection','delivery','task') DEFAULT 'task',
        event_time VARCHAR(10) NULL,
        project_id INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS estimation_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        description VARCHAR(220) NOT NULL,
        unit VARCHAR(80),
        qty DECIMAL(12,2) DEFAULT 0,
        rate DECIMAL(12,2) DEFAULT 0,
        tax DECIMAL(6,2) DEFAULT 0,
        discount DECIMAL(6,2) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ---- Per-project workspace tables --------------------------
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_details (
        project_id INT PRIMARY KEY,
        customer_name VARCHAR(160), customer_company VARCHAR(160), customer_email VARCHAR(160), customer_phone VARCHAR(60),
        owner_name VARCHAR(160), owner_email VARCHAR(160), owner_phone VARCHAR(60), owner_address VARCHAR(220),
        site_address VARCHAR(240), site_city VARCHAR(120), site_state VARCHAR(120), site_pincode VARCHAR(20), site_maplink VARCHAR(400),
        plot_length DECIMAL(12,2) NULL, plot_width DECIMAL(12,2) NULL, plot_area DECIMAL(14,2) NULL, builtup_sqft DECIMAL(14,2) NULL,
        construction_type VARCHAR(120), structure_type VARCHAR(120), foundation_type VARCHAR(120), roofing_type VARCHAR(120),
        num_floors INT NULL, num_units INT NULL, construction_notes TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS project_materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        name VARCHAR(160) NOT NULL,
        category VARCHAR(80),
        quantity DECIMAL(14,2) DEFAULT 0,
        unit VARCHAR(60),
        cost DECIMAL(14,2) DEFAULT 0,
        supplier VARCHAR(160),
        purchase_date DATE NULL,
        used_qty DECIMAL(14,2) DEFAULT 0,
        total_cost DECIMAL(16,2) DEFAULT 0,
        INDEX(project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS project_expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        exp_date DATE NULL,
        category VARCHAR(80),
        description VARCHAR(220),
        amount DECIMAL(16,2) DEFAULT 0,
        INDEX(project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS project_estimates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        description VARCHAR(220) NOT NULL,
        unit VARCHAR(60),
        qty DECIMAL(14,2) DEFAULT 0,
        rate DECIMAL(14,2) DEFAULT 0,
        amount DECIMAL(16,2) DEFAULT 0,
        INDEX(project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS project_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        log_date DATE NULL,
        stage VARCHAR(160),
        percent INT DEFAULT 0,
        status VARCHAR(60),
        note VARCHAR(300),
        INDEX(project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS project_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        category VARCHAR(80),
        doc_date DATE NULL,
        filename VARCHAR(300),
        note VARCHAR(300),
        INDEX(project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS project_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        body TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $log[] = 'All tables created (or already existed).';

    // Ensure the single admin account (only ADMIN_EMAIL can access the Admin Panel)
    $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email=?"); $chk->execute([ADMIN_EMAIL]);
    if (!$chk->fetchColumn()) {
        $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)")
            ->execute(['Administrator', ADMIN_EMAIL, password_hash(ADMIN_DEFAULT_PASSWORD, PASSWORD_DEFAULT), 'Administrator']);
        $log[] = 'Admin account created → ' . ADMIN_EMAIL . ' / ' . ADMIN_DEFAULT_PASSWORD . '  (change the password after logging in).';
    } else {
        $log[] = 'Admin account ' . ADMIN_EMAIL . ' already exists.';
    }
    $userCount = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $log[] = "Total accounts: $userCount.";
} catch (Throwable $ex) {
    $err = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Trackzo Installer</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:system-ui,sans-serif}</style></head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
<div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
  <h1 class="text-2xl font-bold text-slate-900 mb-1">Trackzo Installer</h1>
  <?php if ($err): ?>
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 text-sm">
      <strong>Error:</strong> <?= e($err) ?>
      <p class="mt-2">Check your database details in <code>config.php</code> and reload this page.</p>
    </div>
  <?php else: ?>
    <p class="text-slate-500 text-sm mb-4">Setup complete — your database is ready.</p>
    <ul class="space-y-1.5 text-sm text-slate-700 mb-6">
      <?php foreach ($log as $l): ?><li class="flex gap-2"><span class="text-emerald-500">&#10004;</span><span><?= e($l) ?></span></li><?php endforeach; ?>
    </ul>
    <a href="login.php" class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl">Create your account &rarr;</a>
    <p class="mt-6 text-xs text-rose-500">Security tip: delete <code>install.php</code> from your server after setup.</p>
  <?php endif; ?>
</div>
</body></html>
