<?php
/**
 * Trackzo installer — run ONCE after uploading and setting config.php.
 * Visit  https://yourdomain.com/install.php  in a browser.
 * It creates all tables, seeds sample data, and creates the login user.
 * Safe to re-run: it only seeds tables that are empty.
 */
require_once __DIR__ . '/inc/helpers.php';

$pdo  = db();
$log  = [];
$err  = null;

try {
    // ---- Tables -------------------------------------------------
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(160) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(80) DEFAULT 'Member',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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
        currency VARCHAR(10) DEFAULT 'USD',
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

    $log[] = 'Tables created / verified.';

    // ---- Admin user --------------------------------------------
    $cnt = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($cnt === 0) {
        $st = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)");
        $st->execute(['James Carter', 'james.carter@trackzo.io', password_hash('password123', PASSWORD_DEFAULT), 'Project Manager']);
        $log[] = 'Login user created  ->  james.carter@trackzo.io / password123';
    } else {
        $log[] = 'Users already exist — left untouched.';
    }

    // ---- Seed helper -------------------------------------------
    $seed = function (string $table, array $rows) use ($pdo, &$log) {
        $n = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($n > 0) { $log[] = "`$table` already has data ($n rows) — skipped."; return; }
        if (!$rows) return;
        $cols = array_keys($rows[0]);
        $ph   = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql  = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES $ph";
        $st   = $pdo->prepare($sql);
        foreach ($rows as $r) $st->execute(array_values($r));
        $log[] = "Seeded `$table` with " . count($rows) . ' rows.';
    };

    $seed('clients', [
        ['name'=>'Robert Hargrove','company'=>'Metro Developers Ltd.','email'=>'r.hargrove@metrodev.com','phone'=>'+1 212-555-0181','address'=>'88 Wall Street','city'=>'New York, NY','status'=>'active','joined_date'=>'2022-03-14'],
        ['name'=>'Priya Sharma','company'=>'Apex Realty Group','email'=>'priya@apexrealty.com','phone'=>'+1 718-555-0242','address'=>'200 Industrial Ave','city'=>'Queens, NY','status'=>'active','joined_date'=>'2023-07-02'],
        ['name'=>'William Harrison III','company'=>'Harrison Family Trust','email'=>'wharrison@harrisonft.com','phone'=>'+1 347-555-0305','address'=>'7 Crestwood Lane','city'=>'Brooklyn, NY','status'=>'active','joined_date'=>'2023-05-19'],
        ['name'=>'Maya Cohen','company'=>'Urban Loft Holdings','email'=>'maya@urbanloft.io','phone'=>'+1 201-555-0417','address'=>'85 River St','city'=>'Hoboken, NJ','status'=>'active','joined_date'=>'2024-01-08'],
        ['name'=>'Dr. Samuel Adeyemi','company'=>'City Education Authority','email'=>'s.adeyemi@cea.gov','phone'=>'+1 718-555-0560','address'=>'1 Academy Road','city'=>'The Bronx, NY','status'=>'active','joined_date'=>'2024-09-15'],
        ['name'=>'Linda Kwong','company'=>'Pacific Hospitality Group','email'=>'lkwong@pacifichg.com','phone'=>'+1 212-555-0648','address'=>'300 Broadway','city'=>'Manhattan, NY','status'=>'inactive','joined_date'=>'2023-11-20'],
    ]);

    $seed('projects', [
        ['name'=>'Skyline Tower Residences','client_id'=>1,'site_address'=>'14 Harbor Blvd, Downtown, NY 10001','type'=>'Residential High-Rise','status'=>'active','budget'=>4800000,'spent'=>2940000,'progress'=>62,'start_date'=>'2024-02-01','end_date'=>'2025-09-30','area'=>18400,'floors'=>22,'manager'=>'James Carter','description'=>'22-floor luxury residential tower with 88 units, rooftop amenities, and underground parking.'],
        ['name'=>'Greenfield Commercial Park','client_id'=>2,'site_address'=>'200 Industrial Ave, Queens, NY 11101','type'=>'Commercial Complex','status'=>'active','budget'=>2600000,'spent'=>980000,'progress'=>38,'start_date'=>'2024-06-15','end_date'=>'2025-12-31','area'=>9200,'floors'=>4,'manager'=>'Sarah Mitchell','description'=>'Multi-unit commercial park with office spaces, retail units, and shared amenities.'],
        ['name'=>'Sunrise Villa Estate','client_id'=>3,'site_address'=>'7 Crestwood Lane, Brooklyn, NY 11215','type'=>'Luxury Villa','status'=>'completed','budget'=>920000,'spent'=>905000,'progress'=>100,'start_date'=>'2023-08-01','end_date'=>'2024-07-31','area'=>4800,'floors'=>3,'manager'=>'James Carter','description'=>'Bespoke luxury villa with pool, home theatre, smart home systems, and landscaped grounds.'],
        ['name'=>'Riverfront Warehouse Conversion','client_id'=>4,'site_address'=>'85 River St, Hoboken, NJ 07030','type'=>'Renovation','status'=>'on-hold','budget'=>1400000,'spent'=>320000,'progress'=>23,'start_date'=>'2024-04-01','end_date'=>'2025-06-30','area'=>6200,'floors'=>5,'manager'=>'Sarah Mitchell','description'=>'Historic warehouse conversion to mixed-use loft apartments and ground-floor retail.'],
        ['name'=>'Northgate School Extension','client_id'=>5,'site_address'=>'1 Academy Road, The Bronx, NY 10451','type'=>'Institutional','status'=>'planning','budget'=>3200000,'spent'=>45000,'progress'=>5,'start_date'=>'2025-01-15','end_date'=>'2026-06-30','area'=>11000,'floors'=>3,'manager'=>'David Okonkwo','description'=>'New STEM block, sports hall, and cafeteria extension for Northgate Academy.'],
    ]);

    $seed('materials', [
        ['name'=>'Portland Cement (OPC 53)','category'=>'Cement','unit'=>'Bags (50kg)','stock'=>850,'min_stock'=>300,'rate'=>12.50,'supplier'=>'Atlas Cement Co.','last_updated'=>'2025-07-28'],
        ['name'=>'Steel Rebar (12mm)','category'=>'Steel','unit'=>'MT','stock'=>42,'min_stock'=>15,'rate'=>820,'supplier'=>'Ironclad Steel Works','last_updated'=>'2025-07-30'],
        ['name'=>'Coarse Aggregate (20mm)','category'=>'Aggregate','unit'=>'Cubic Yard','stock'=>180,'min_stock'=>80,'rate'=>65,'supplier'=>'Valley Quarry LLC','last_updated'=>'2025-07-25'],
        ['name'=>'Fine Sand (Washed)','category'=>'Aggregate','unit'=>'Cubic Yard','stock'=>95,'min_stock'=>100,'rate'=>55,'supplier'=>'Valley Quarry LLC','last_updated'=>'2025-07-25'],
        ['name'=>'Red Clay Bricks','category'=>'Masonry','unit'=>'Thousand','stock'=>28,'min_stock'=>10,'rate'=>480,'supplier'=>'Heritage Brick Co.','last_updated'=>'2025-07-22'],
        ['name'=>'Structural Timber (4x4)','category'=>'Timber','unit'=>'Board Ft','stock'=>3200,'min_stock'=>1000,'rate'=>3.20,'supplier'=>'Pacific Lumber Inc.','last_updated'=>'2025-07-29'],
        ['name'=>'PVC Pipes (4")','category'=>'Plumbing','unit'=>'Length (20ft)','stock'=>12,'min_stock'=>40,'rate'=>28,'supplier'=>'FlowTech Supplies','last_updated'=>'2025-07-18'],
        ['name'=>'Electrical Conduit (1")','category'=>'Electrical','unit'=>'Roll (100m)','stock'=>8,'min_stock'=>20,'rate'=>145,'supplier'=>'Voltex Electrical','last_updated'=>'2025-07-20'],
        ['name'=>'Plywood (3/4" Marine)','category'=>'Timber','unit'=>'Sheet','stock'=>220,'min_stock'=>80,'rate'=>62,'supplier'=>'Pacific Lumber Inc.','last_updated'=>'2025-07-27'],
        ['name'=>'Portland Cement (White)','category'=>'Cement','unit'=>'Bags (25kg)','stock'=>5,'min_stock'=>50,'rate'=>22,'supplier'=>'Atlas Cement Co.','last_updated'=>'2025-07-15'],
    ]);

    $seed('purchase_orders', [
        ['supplier'=>'Atlas Cement Co.','item'=>'Portland Cement OPC 53','qty'=>500,'rate'=>12.50,'total'=>6250,'status'=>'delivered','order_date'=>'2025-07-20','expected_date'=>'2025-07-25','project_id'=>1],
        ['supplier'=>'Ironclad Steel Works','item'=>'Steel Rebar 12mm','qty'=>20,'rate'=>820,'total'=>16400,'status'=>'approved','order_date'=>'2025-07-28','expected_date'=>'2025-08-05','project_id'=>1],
        ['supplier'=>'Valley Quarry LLC','item'=>'Coarse Aggregate 20mm','qty'=>80,'rate'=>65,'total'=>8500,'status'=>'pending','order_date'=>'2025-07-30','expected_date'=>'2025-08-08','project_id'=>2],
        ['supplier'=>'FlowTech Supplies','item'=>'PVC Pipes 4in','qty'=>60,'rate'=>28,'total'=>1680,'status'=>'pending','order_date'=>'2025-07-31','expected_date'=>'2025-08-10','project_id'=>2],
        ['supplier'=>'Heritage Brick Co.','item'=>'Red Clay Bricks','qty'=>20,'rate'=>480,'total'=>9600,'status'=>'cancelled','order_date'=>'2025-07-10','expected_date'=>'2025-07-18','project_id'=>4],
    ]);

    $seed('transactions', [
        ['txn_date'=>'2025-07-31','description'=>'Client Payment - Skyline Tower Phase 3','category'=>'Client Receipt','type'=>'income','amount'=>480000,'status'=>'paid','project_id'=>1],
        ['txn_date'=>'2025-07-29','description'=>'Steel Procurement - Ironclad Steel','category'=>'Materials','type'=>'expense','amount'=>42600,'status'=>'paid','project_id'=>1],
        ['txn_date'=>'2025-07-28','description'=>'Labour Wages - July W4','category'=>'Labour','type'=>'expense','amount'=>28400,'status'=>'paid','project_id'=>1],
        ['txn_date'=>'2025-07-25','description'=>'Client Payment - Greenfield Park Milestone 1','category'=>'Client Receipt','type'=>'income','amount'=>240000,'status'=>'paid','project_id'=>2],
        ['txn_date'=>'2025-07-22','description'=>'Subcontractor - MEP Works','category'=>'Subcontractor','type'=>'expense','amount'=>85000,'status'=>'paid','project_id'=>2],
        ['txn_date'=>'2025-07-20','description'=>'Equipment Rental - Tower Crane','category'=>'Equipment','type'=>'expense','amount'=>18500,'status'=>'paid','project_id'=>1],
        ['txn_date'=>'2025-07-18','description'=>'Pending Invoice - Greenfield Phase 2','category'=>'Client Receipt','type'=>'income','amount'=>180000,'status'=>'pending','project_id'=>2],
        ['txn_date'=>'2025-07-15','description'=>'Concrete Mix Supply - Atlas Cement','category'=>'Materials','type'=>'expense','amount'=>12800,'status'=>'paid','project_id'=>1],
        ['txn_date'=>'2025-07-12','description'=>'Insurance Premium - Project Coverage','category'=>'Insurance','type'=>'expense','amount'=>6200,'status'=>'paid','project_id'=>1],
        ['txn_date'=>'2025-07-08','description'=>'Client Payment - Sunrise Villa Final','category'=>'Client Receipt','type'=>'income','amount'=>92000,'status'=>'paid','project_id'=>3],
        ['txn_date'=>'2025-07-05','description'=>'Overdue Payment - Urban Loft Deposit','category'=>'Client Receipt','type'=>'income','amount'=>140000,'status'=>'overdue','project_id'=>4],
        ['txn_date'=>'2025-07-03','description'=>'Labour Wages - June Final','category'=>'Labour','type'=>'expense','amount'=>31200,'status'=>'paid','project_id'=>1],
    ]);

    $seed('accounts', [
        ['name'=>'Chase Business Checking','type'=>'bank','balance'=>842600,'currency'=>'USD','last_transaction'=>'2025-07-31'],
        ['name'=>'Citibank Operations Account','type'=>'bank','balance'=>225000,'currency'=>'USD','last_transaction'=>'2025-07-29'],
        ['name'=>'Petty Cash - Site Office','type'=>'cash','balance'=>4200,'currency'=>'USD','last_transaction'=>'2025-07-30'],
        ['name'=>'AmEx Business Platinum','type'=>'credit','balance'=>-32800,'currency'=>'USD','last_transaction'=>'2025-07-28'],
    ]);

    $seed('calendar_events', [
        ['title'=>'Foundation Inspection - Skyline','event_date'=>'2025-08-04','type'=>'inspection','event_time'=>'09:00','project_id'=>1],
        ['title'=>'Client Meeting - Apex Realty','event_date'=>'2025-08-06','type'=>'meeting','event_time'=>'14:00','project_id'=>2],
        ['title'=>'Steel Delivery - Ironclad','event_date'=>'2025-08-05','type'=>'delivery','event_time'=>'08:00','project_id'=>1],
        ['title'=>'Phase 3 Deadline - Skyline','event_date'=>'2025-08-15','type'=>'deadline','event_time'=>null,'project_id'=>1],
        ['title'=>'Safety Audit - Greenfield','event_date'=>'2025-08-12','type'=>'inspection','event_time'=>'10:00','project_id'=>2],
        ['title'=>'Concrete Pour - Level 14','event_date'=>'2025-08-08','type'=>'task','event_time'=>'07:00','project_id'=>1],
        ['title'=>'Subcontractor Review','event_date'=>'2025-08-20','type'=>'meeting','event_time'=>'11:00','project_id'=>null],
        ['title'=>'Material Delivery - Valley Quarry','event_date'=>'2025-08-08','type'=>'delivery','event_time'=>'13:00','project_id'=>2],
        ['title'=>'Northgate Site Survey','event_date'=>'2025-08-22','type'=>'inspection','event_time'=>'09:30','project_id'=>5],
        ['title'=>'Progress Report - Metro Dev','event_date'=>'2025-08-25','type'=>'meeting','event_time'=>'15:00','project_id'=>1],
    ]);

    $seed('estimation_items', [
        ['description'=>'Excavation & Earth Work','unit'=>'Cubic Yard','qty'=>1200,'rate'=>18,'tax'=>8,'discount'=>0],
        ['description'=>'Concrete Foundation (M30)','unit'=>'Cubic Meter','qty'=>480,'rate'=>145,'tax'=>8,'discount'=>2],
        ['description'=>'Steel Reinforcement','unit'=>'Metric Ton','qty'=>42,'rate'=>1200,'tax'=>8,'discount'=>0],
        ['description'=>'Brick Masonry Work','unit'=>'Sq.Ft.','qty'=>18000,'rate'=>12,'tax'=>8,'discount'=>5],
        ['description'=>'Plastering (Internal)','unit'=>'Sq.Ft.','qty'=>32000,'rate'=>4.50,'tax'=>8,'discount'=>0],
    ]);

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
    <p class="text-slate-500 text-sm mb-4">Setup completed successfully.</p>
    <ul class="space-y-1.5 text-sm text-slate-700 mb-6">
      <?php foreach ($log as $l): ?><li class="flex gap-2"><span class="text-emerald-500">&#10004;</span><span><?= e($l) ?></span></li><?php endforeach; ?>
    </ul>
    <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800 mb-6">
      <p class="font-semibold mb-1">Login details</p>
      <p>Email: <strong>james.carter@trackzo.io</strong></p>
      <p>Password: <strong>password123</strong></p>
      <p class="mt-2 text-blue-600">Change this in Settings after logging in.</p>
    </div>
    <a href="login.php" class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl">Go to Login &rarr;</a>
    <p class="mt-6 text-xs text-rose-500">Security: delete <code>install.php</code> from your server after setup.</p>
  <?php endif; ?>
</div>
</body></html>
