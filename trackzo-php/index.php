<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

$projCount   = (int) $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$activeCount = (int) $db->query("SELECT COUNT(*) FROM projects WHERE status='active'")->fetchColumn();
$totBudget   = (float) $db->query("SELECT COALESCE(SUM(budget),0) FROM projects")->fetchColumn();
$totSpent    = (float) $db->query("SELECT COALESCE(SUM(spent),0) FROM projects")->fetchColumn();
$income      = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND status='paid'")->fetchColumn();
$expense     = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
$pending     = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status IN('pending','overdue')")->fetchColumn();
$overdueCnt  = (int) $db->query("SELECT COUNT(*) FROM transactions WHERE status='overdue'")->fetchColumn();
$overallProgress = $totBudget > 0 ? round($totSpent / $totBudget * 100) : 0;

// Project status distribution (donut)
$statusRows = $db->query("SELECT status, COUNT(*) c FROM projects GROUP BY status")->fetchAll();
$statusMap  = ['active'=>0,'completed'=>0,'planning'=>0,'on-hold'=>0];
foreach ($statusRows as $r) $statusMap[$r['status']] = (int) $r['c'];
$statusColors = ['active'=>'#1D4ED8','completed'=>'#10B981','planning'=>'#F59E0B','on-hold'=>'#EF4444'];
$statusLabels = ['active'=>'Active','completed'=>'Completed','planning'=>'Planning','on-hold'=>'On Hold'];
$statusReal   = array_sum($statusMap);
$statusTotal  = max(1, $statusReal);
// build conic-gradient
$acc = 0; $segments = [];
foreach ($statusMap as $k => $v) {
    $start = $acc / $statusTotal * 360;
    $acc += $v;
    $end = $acc / $statusTotal * 360;
    $segments[] = $statusColors[$k] . " {$start}deg {$end}deg";
}
$conic = $statusReal > 0 ? 'conic-gradient(' . implode(',', $segments) . ')' : '#E2E8F0';

// Monthly income vs expense from transactions
$txns = $db->query("SELECT txn_date, type, amount FROM transactions")->fetchAll();
$byMonth = [];
foreach ($txns as $t) {
    $m = date('M', strtotime($t['txn_date']));
    $k = date('Y-m', strtotime($t['txn_date']));
    if (!isset($byMonth[$k])) $byMonth[$k] = ['label'=>$m,'income'=>0,'expense'=>0];
    $byMonth[$k][$t['type']] += (float) $t['amount'];
}
ksort($byMonth);
$byMonth = array_slice($byMonth, -6, 6, true);
$maxVal  = 1;
foreach ($byMonth as $mm) $maxVal = max($maxVal, $mm['income'], $mm['expense']);

$recentProjects = $db->query("SELECT * FROM projects ORDER BY id DESC LIMIT 4")->fetchAll();
$recentTxns     = $db->query("SELECT * FROM transactions ORDER BY txn_date DESC, id DESC LIMIT 5")->fetchAll();

$statusBadge = [
    'active'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700',
    'on-hold'=>'bg-amber-100 text-amber-700','planning'=>'bg-slate-100 text-slate-600',
];

$stats = [
    ['label'=>'Total Projects','value'=>$projCount,'sub'=>$activeCount.' active','icon'=>'folder','bg'=>'bg-blue-50','fg'=>'text-blue-600'],
    ['label'=>'Total Budget','value'=>money_short($totBudget),'sub'=>'Across all projects','icon'=>'dollar','bg'=>'bg-emerald-50','fg'=>'text-emerald-600'],
    ['label'=>'Total Spent','value'=>money_short($totSpent),'sub'=>$overallProgress.'% of budget','icon'=>'trending-down','bg'=>'bg-rose-50','fg'=>'text-rose-600'],
    ['label'=>'Income (paid)','value'=>money_short($income),'sub'=>'Received to date','icon'=>'trending-up','bg'=>'bg-teal-50','fg'=>'text-teal-600'],
    ['label'=>'Expenses','value'=>money_short($expense),'sub'=>'All transactions','icon'=>'package','bg'=>'bg-violet-50','fg'=>'text-violet-600'],
    ['label'=>'Pending Payments','value'=>money_short($pending),'sub'=>$overdueCnt.' overdue','icon'=>'alert-circle','bg'=>'bg-orange-50','fg'=>'text-orange-600'],
];

$page = 'dashboard'; $page_title = 'Dashboard';
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 space-y-6">

  <!-- Stats -->
  <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    <?php foreach ($stats as $s): ?>
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-9 h-9 rounded-xl <?= $s['bg'] ?> <?= $s['fg'] ?> flex items-center justify-center mb-3"><?= icon($s['icon'], 18) ?></div>
        <p class="text-2xl font-bold text-slate-900 mb-0.5 font-display"><?= e($s['value']) ?></p>
        <p class="text-xs text-slate-500 mb-1"><?= e($s['label']) ?></p>
        <p class="text-[11px] text-slate-400"><?= e($s['sub']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Charts row -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="font-bold text-slate-900 text-sm font-display">Cash Flow</h3>
          <p class="text-xs text-slate-400 mt-0.5">Monthly income vs. expenses</p>
        </div>
        <span class="text-xs bg-blue-50 text-blue-600 font-semibold px-2.5 py-1 rounded-lg"><?= date('Y') ?></span>
      </div>
      <div class="flex items-end gap-4 h-52 pt-4">
        <?php foreach ($byMonth as $mm): ?>
          <div class="flex-1 flex flex-col items-center justify-end h-full gap-1">
            <div class="flex items-end gap-1 w-full justify-center h-full">
              <div class="w-1/2 max-w-[22px] rounded-t-md bg-blue-500" style="height:<?= max(2,$mm['income']/$maxVal*100) ?>%" title="Income <?= money($mm['income']) ?>"></div>
              <div class="w-1/2 max-w-[22px] rounded-t-md bg-rose-400" style="height:<?= max(2,$mm['expense']/$maxVal*100) ?>%" title="Expense <?= money($mm['expense']) ?>"></div>
            </div>
            <span class="text-[11px] text-slate-400"><?= e($mm['label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="flex items-center gap-4 mt-3 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>Income</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>Expense</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <h3 class="font-bold text-slate-900 text-sm mb-1 font-display">Project Status</h3>
      <p class="text-xs text-slate-400 mb-4">All <?= $projCount ?> projects</p>
      <div class="flex justify-center my-2">
        <div class="rounded-full" style="width:150px;height:150px;background:<?= $conic ?>">
          <div class="w-full h-full flex items-center justify-center">
            <div class="bg-white rounded-full flex items-center justify-center" style="width:84px;height:84px">
              <span class="text-lg font-bold text-slate-800 font-display"><?= $projCount ?></span>
            </div>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-1.5 mt-3">
        <?php foreach ($statusMap as $k => $v): ?>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:<?= $statusColors[$k] ?>"></span>
            <span class="text-xs text-slate-500"><?= $statusLabels[$k] ?>: <strong class="text-slate-700"><?= $v ?></strong></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Bottom row -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-900 text-sm font-display">Recent Projects</h3>
        <a href="projects.php" class="text-xs text-blue-600 font-semibold flex items-center gap-0.5 hover:underline">View all <?= icon('arrow-up-right',12) ?></a>
      </div>
      <div class="space-y-3">
        <?php foreach ($recentProjects as $p): ?>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-blue-600 flex-shrink-0"><?= icon('folder',16) ?></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-800 truncate"><?= e($p['name']) ?></p>
              <div class="flex items-center gap-2 mt-0.5">
                <div class="flex-1 bg-slate-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-blue-500" style="width:<?= (int)$p['progress'] ?>%"></div></div>
                <span class="text-[11px] text-slate-500 whitespace-nowrap"><?= (int)$p['progress'] ?>%</span>
              </div>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg whitespace-nowrap <?= $statusBadge[$p['status']] ?? 'bg-slate-100 text-slate-600' ?>"><?= ucfirst($p['status']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-900 text-sm font-display">Recent Transactions</h3>
        <a href="finance.php" class="text-xs text-blue-600 font-semibold flex items-center gap-0.5 hover:underline">View all <?= icon('arrow-up-right',12) ?></a>
      </div>
      <div class="space-y-3">
        <?php foreach ($recentTxns as $t): $inc = $t['type']==='income'; ?>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 <?= $inc?'bg-emerald-50 text-emerald-600':'bg-rose-50 text-rose-500' ?>"><?= icon($inc?'trending-up':'trending-down',15) ?></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-slate-800 truncate"><?= e($t['description']) ?></p>
              <p class="text-[11px] text-slate-400"><?= e($t['txn_date']) ?> · <?= e($t['category']) ?></p>
            </div>
            <span class="text-sm font-bold whitespace-nowrap <?= $inc?'text-emerald-600':'text-rose-500' ?>"><?= $inc?'+':'-' ?><?= money_short($t['amount']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Overall progress -->
  <div class="rounded-2xl p-5 text-white flex items-center gap-6" style="background:linear-gradient(135deg,#0F2B4C 0%,#1D4ED8 100%)">
    <div class="flex-1">
      <p class="text-sm font-semibold text-blue-200 mb-1">Overall Portfolio Progress</p>
      <p class="text-3xl font-bold mb-3 font-display"><?= $overallProgress ?>%</p>
      <div class="bg-white/20 rounded-full h-2"><div class="h-2 rounded-full bg-white" style="width:<?= $overallProgress ?>%"></div></div>
      <p class="text-xs text-blue-200 mt-2"><?= money_short($totSpent) ?> spent of <?= money_short($totBudget) ?> total budget</p>
    </div>
    <div class="hidden sm:grid grid-cols-2 gap-4 text-center">
      <div><p class="text-2xl font-bold"><?= money_short($totBudget-$totSpent) ?></p><p class="text-xs text-blue-200">Remaining</p></div>
      <div><p class="text-2xl font-bold"><?= $projCount ?></p><p class="text-xs text-blue-200">Projects</p></div>
    </div>
  </div>

</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
