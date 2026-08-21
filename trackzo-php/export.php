<?php
/**
 * Report exporter.
 *   export.php?format=xls   -> downloads an Excel workbook (.xls)
 *   export.php?format=pdf   -> opens a clean printable page (Save as PDF)
 */
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

$format = ($_GET['format'] ?? 'pdf') === 'xls' ? 'xls' : 'pdf';

// ---- Gather report data -------------------------------------------
$income  = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income'")->fetchColumn();
$expense = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
$pending = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status IN('pending','overdue')")->fetchColumn();
$net     = $income - $expense;

$byCat = $db->query("SELECT category, SUM(amount) total FROM transactions WHERE type='expense' GROUP BY category ORDER BY total DESC")->fetchAll();
$projUtil = $db->query("SELECT name, budget, spent FROM projects ORDER BY budget DESC")->fetchAll();
$topClients = $db->query("
  SELECT c.name, c.company, COALESCE(SUM(p.budget),0) val, COUNT(p.id) pc
  FROM clients c LEFT JOIN projects p ON p.client_id=c.id
  GROUP BY c.id ORDER BY val DESC")->fetchAll();
$txns = $db->query("
  SELECT t.txn_date, t.description, t.category, t.type, t.amount, t.status, p.name pname
  FROM transactions t LEFT JOIN projects p ON p.id=t.project_id
  ORDER BY t.txn_date DESC, t.id DESC")->fetchAll();

$generated = date('d M Y, H:i');
$cur = APP_CURRENCY;

/* =====================================================================
 *  EXCEL (.xls)  — HTML-table workbook that Excel opens natively.
 *  Amount columns are raw numbers so Excel can SUM them.
 * ===================================================================*/
if ($format === 'xls') {
    $file = 'trackzo-report-' . date('Y-m-d') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Cache-Control: max-age=0');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel shows the ₹ symbol correctly
    $th = 'style="background:#0F2B4C;color:#fff;font-weight:bold;border:1px solid #ccc;padding:4px"';
    $td = 'style="border:1px solid #ddd;padding:4px"';
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head><meta charset="UTF-8"></head><body>
    <table>
      <tr><td colspan="6" style="font-size:16px;font-weight:bold">Trackzo — Business Report</td></tr>
      <tr><td colspan="6">Generated: <?= e($generated) ?> (all amounts in <?= e($cur) ?>)</td></tr>
    </table>
    <br>
    <table border="1"><tr><td colspan="2" <?= $th ?>>FINANCIAL SUMMARY</td></tr>
      <tr><td <?= $td ?>>Total Income (<?= $cur ?>)</td><td <?= $td ?>><?= round($income,2) ?></td></tr>
      <tr><td <?= $td ?>>Total Expense (<?= $cur ?>)</td><td <?= $td ?>><?= round($expense,2) ?></td></tr>
      <tr><td <?= $td ?>>Pending / Overdue (<?= $cur ?>)</td><td <?= $td ?>><?= round($pending,2) ?></td></tr>
      <tr><td <?= $td ?>><b>Net Position (<?= $cur ?>)</b></td><td <?= $td ?>><b><?= round($net,2) ?></b></td></tr>
    </table>
    <br>
    <table border="1"><tr><td colspan="2" <?= $th ?>>EXPENSES BY CATEGORY</td></tr>
      <tr><td <?= $th ?>>Category</td><td <?= $th ?>>Amount (<?= $cur ?>)</td></tr>
      <?php foreach ($byCat as $c): ?><tr><td <?= $td ?>><?= e($c['category']) ?></td><td <?= $td ?>><?= round($c['total'],2) ?></td></tr><?php endforeach; ?>
    </table>
    <br>
    <table border="1"><tr><td colspan="4" <?= $th ?>>PROJECT BUDGET UTILIZATION</td></tr>
      <tr><td <?= $th ?>>Project</td><td <?= $th ?>>Budget (<?= $cur ?>)</td><td <?= $th ?>>Spent (<?= $cur ?>)</td><td <?= $th ?>>Used %</td></tr>
      <?php foreach ($projUtil as $p): $pct=(float)$p['budget']>0?round((float)$p['spent']/(float)$p['budget']*100):0; ?>
        <tr><td <?= $td ?>><?= e($p['name']) ?></td><td <?= $td ?>><?= round($p['budget'],2) ?></td><td <?= $td ?>><?= round($p['spent'],2) ?></td><td <?= $td ?>><?= $pct ?></td></tr>
      <?php endforeach; ?>
    </table>
    <br>
    <table border="1"><tr><td colspan="4" <?= $th ?>>TOP CLIENTS</td></tr>
      <tr><td <?= $th ?>>Client</td><td <?= $th ?>>Company</td><td <?= $th ?>>Projects</td><td <?= $th ?>>Portfolio Value (<?= $cur ?>)</td></tr>
      <?php foreach ($topClients as $c): ?><tr><td <?= $td ?>><?= e($c['name']) ?></td><td <?= $td ?>><?= e($c['company']) ?></td><td <?= $td ?>><?= (int)$c['pc'] ?></td><td <?= $td ?>><?= round($c['val'],2) ?></td></tr><?php endforeach; ?>
    </table>
    <br>
    <table border="1"><tr><td colspan="6" <?= $th ?>>TRANSACTIONS LEDGER</td></tr>
      <tr><td <?= $th ?>>Date</td><td <?= $th ?>>Description</td><td <?= $th ?>>Category</td><td <?= $th ?>>Project</td><td <?= $th ?>>Type</td><td <?= $th ?>>Amount (<?= $cur ?>)</td></tr>
      <?php foreach ($txns as $t): ?>
        <tr><td <?= $td ?>><?= e($t['txn_date']) ?></td><td <?= $td ?>><?= e($t['description']) ?></td><td <?= $td ?>><?= e($t['category']) ?></td><td <?= $td ?>><?= e($t['pname'] ?: '-') ?></td><td <?= $td ?>><?= ucfirst($t['type']) ?></td><td <?= $td ?>><?= round($t['amount'],2) ?></td></tr>
      <?php endforeach; ?>
    </table>
    </body></html>
    <?php
    exit;
}

/* =====================================================================
 *  PDF  — clean printable page; browser "Save as PDF".
 * ===================================================================*/
$mny = fn($v) => $cur . inr_group($v);
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Trackzo Report — <?= date('Y-m-d') ?></title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',Arial,sans-serif;color:#0f172a;background:#fff;padding:32px;font-size:12px}
  .bar{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #1D4ED8;padding-bottom:14px;margin-bottom:20px}
  .brand{display:flex;align-items:center;gap:10px}
  .logo{width:38px;height:38px;border-radius:9px;background:#1D4ED8;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px}
  h1{font-size:20px;color:#0F2B4C}
  .sub{color:#64748b;font-size:11px}
  h2{font-size:13px;color:#0F2B4C;margin:22px 0 8px;padding-bottom:5px;border-bottom:1px solid #e2e8f0;text-transform:uppercase;letter-spacing:.04em}
  table{width:100%;border-collapse:collapse;margin-top:6px}
  th{background:#0F2B4C;color:#fff;text-align:left;padding:7px 9px;font-size:11px}
  td{padding:6px 9px;border-bottom:1px solid #eef2f7}
  tr:nth-child(even) td{background:#f8fafc}
  .r{text-align:right}.c{text-align:center}
  .cards{display:flex;gap:12px;margin-bottom:6px}
  .card{flex:1;border:1px solid #e2e8f0;border-radius:10px;padding:12px}
  .card .k{font-size:10px;color:#64748b;text-transform:uppercase}
  .card .v{font-size:18px;font-weight:800;color:#0F2B4C;margin-top:3px}
  .pos{color:#059669}.neg{color:#e11d48}
  .toolbar{position:fixed;top:14px;right:14px;display:flex;gap:8px}
  .btn{background:#1D4ED8;color:#fff;border:0;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none}
  .btn.grey{background:#e2e8f0;color:#334155}
  @media print{.toolbar{display:none}body{padding:0}}
</style></head>
<body>
  <div class="toolbar">
    <button class="btn" onclick="window.print()">🖨 Save as PDF</button>
    <a class="btn grey" href="reports.php">Back</a>
  </div>

  <div class="bar">
    <div class="brand"><div class="logo">T</div><div><h1>Trackzo — Business Report</h1><div class="sub">Construction ERP · all amounts in Indian Rupees (<?= $cur ?>)</div></div></div>
    <div class="sub" style="text-align:right">Generated<br><strong><?= e($generated) ?></strong></div>
  </div>

  <div class="cards">
    <div class="card"><div class="k">Total Income</div><div class="v pos"><?= $mny($income) ?></div></div>
    <div class="card"><div class="k">Total Expense</div><div class="v neg"><?= $mny($expense) ?></div></div>
    <div class="card"><div class="k">Pending / Overdue</div><div class="v"><?= $mny($pending) ?></div></div>
    <div class="card"><div class="k">Net Position</div><div class="v <?= $net<0?'neg':'pos' ?>"><?= $mny($net) ?></div></div>
  </div>

  <h2>Expenses by Category</h2>
  <table><thead><tr><th>Category</th><th class="r">Amount</th></tr></thead><tbody>
    <?php foreach ($byCat as $c): ?><tr><td><?= e($c['category']) ?></td><td class="r"><?= $mny($c['total']) ?></td></tr><?php endforeach; ?>
  </tbody></table>

  <h2>Project Budget Utilization</h2>
  <table><thead><tr><th>Project</th><th class="r">Budget</th><th class="r">Spent</th><th class="r">Remaining</th><th class="c">Used %</th></tr></thead><tbody>
    <?php foreach ($projUtil as $p): $pct=(float)$p['budget']>0?round((float)$p['spent']/(float)$p['budget']*100):0; ?>
      <tr><td><?= e($p['name']) ?></td><td class="r"><?= $mny($p['budget']) ?></td><td class="r"><?= $mny($p['spent']) ?></td><td class="r"><?= $mny((float)$p['budget']-(float)$p['spent']) ?></td><td class="c"><?= $pct ?>%</td></tr>
    <?php endforeach; ?>
  </tbody></table>

  <h2>Top Clients by Portfolio Value</h2>
  <table><thead><tr><th>Client</th><th>Company</th><th class="c">Projects</th><th class="r">Portfolio Value</th></tr></thead><tbody>
    <?php foreach ($topClients as $c): ?><tr><td><?= e($c['name']) ?></td><td><?= e($c['company']) ?></td><td class="c"><?= (int)$c['pc'] ?></td><td class="r"><?= $mny($c['val']) ?></td></tr><?php endforeach; ?>
  </tbody></table>

  <h2>Transactions Ledger</h2>
  <table><thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Type</th><th class="r">Amount</th></tr></thead><tbody>
    <?php foreach ($txns as $t): ?>
      <tr><td><?= e($t['txn_date']) ?></td><td><?= e($t['description']) ?></td><td><?= e($t['category']) ?></td><td><?= ucfirst($t['type']) ?></td><td class="r <?= $t['type']==='income'?'pos':'neg' ?>"><?= ($t['type']==='income'?'+':'-').$mny($t['amount']) ?></td></tr>
    <?php endforeach; ?>
  </tbody></table>

  <script>window.addEventListener('load',function(){setTimeout(function(){window.print();},400);});</script>
</body></html>
