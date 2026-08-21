<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

$byCat = $db->query("SELECT category, SUM(amount) total FROM transactions WHERE type='expense' GROUP BY category ORDER BY total DESC")->fetchAll();
$catMax = 1; foreach ($byCat as $c) $catMax = max($catMax, (float)$c['total']);
$catColors = ['#1D4ED8','#10B981','#F59E0B','#EF4444','#8B5CF6','#0EA5E9','#EC4899','#14B8A6'];

$projUtil = $db->query("SELECT name, budget, spent FROM projects ORDER BY budget DESC")->fetchAll();

$topClients = $db->query("
  SELECT c.name, c.company, COALESCE(SUM(p.budget),0) val, COUNT(p.id) pc
  FROM clients c LEFT JOIN projects p ON p.client_id=c.id
  GROUP BY c.id ORDER BY val DESC LIMIT 5")->fetchAll();

$income  = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income'")->fetchColumn();
$expense = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
$profit  = $income - $expense;

$page = 'reports'; $page_title = 'Reports';
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-base font-bold text-slate-800 font-display">Business Report</h2>
      <p class="text-xs text-slate-400">Financial summary, budgets, clients & ledger — all amounts in ₹</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="export.php?format=xls" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm">
        <?= icon('bar-chart',16) ?> Export Excel
      </a>
      <a href="export.php?format=pdf" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl shadow-sm">
        <?= icon('arrow-up-right',16) ?> Export PDF
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5"><p class="text-xs text-slate-500 mb-1">Total Income</p><p class="text-2xl font-bold text-emerald-600 font-display"><?= money($income) ?></p></div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5"><p class="text-xs text-slate-500 mb-1">Total Expense</p><p class="text-2xl font-bold text-rose-500 font-display"><?= money($expense) ?></p></div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5"><p class="text-xs text-slate-500 mb-1">Net Position</p><p class="text-2xl font-bold font-display <?= $profit<0?'text-rose-500':'text-slate-900' ?>"><?= money($profit) ?></p></div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <h3 class="font-bold text-slate-900 text-sm mb-4 font-display">Expenses by Category</h3>
      <div class="space-y-3">
        <?php foreach ($byCat as $i => $c): ?>
          <div>
            <div class="flex justify-between text-xs mb-1"><span class="text-slate-600 font-medium"><?= e($c['category']) ?></span><span class="text-slate-500"><?= money($c['total']) ?></span></div>
            <div class="bg-slate-100 rounded-full h-2"><div class="h-2 rounded-full" style="width:<?= (float)$c['total']/$catMax*100 ?>%;background:<?= $catColors[$i % count($catColors)] ?>"></div></div>
          </div>
        <?php endforeach; ?>
        <?php if (!$byCat): ?><p class="text-sm text-slate-400">No expense data.</p><?php endif; ?>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <h3 class="font-bold text-slate-900 text-sm mb-4 font-display">Budget Utilization</h3>
      <div class="space-y-3">
        <?php foreach ($projUtil as $p): $pct = (float)$p['budget']>0 ? min(100,(float)$p['spent']/(float)$p['budget']*100) : 0; $over = $pct>=90; ?>
          <div>
            <div class="flex justify-between text-xs mb-1"><span class="text-slate-600 font-medium truncate pr-2"><?= e($p['name']) ?></span><span class="text-slate-500 whitespace-nowrap"><?= round($pct) ?>%</span></div>
            <div class="bg-slate-100 rounded-full h-2"><div class="h-2 rounded-full <?= $over?'bg-rose-500':'bg-blue-500' ?>" style="width:<?= $pct ?>%"></div></div>
          </div>
        <?php endforeach; ?>
        <?php if (!$projUtil): ?><p class="text-sm text-slate-400">No projects.</p><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900 text-sm font-display">Top Clients by Portfolio Value</h3></div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
      <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
        <th class="px-5 py-3 font-semibold">Client</th><th class="px-5 py-3 font-semibold text-center">Projects</th><th class="px-5 py-3 font-semibold text-right">Portfolio Value</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($topClients as $c): ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3"><p class="font-semibold text-slate-800"><?= e($c['name']) ?></p><p class="text-xs text-slate-400"><?= e($c['company']) ?></p></td>
            <td class="px-5 py-3 text-center text-slate-600"><?= (int)$c['pc'] ?></td>
            <td class="px-5 py-3 text-right font-semibold text-slate-800"><?= money($c['val']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
