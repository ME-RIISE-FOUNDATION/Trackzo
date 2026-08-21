<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $type = ($_POST['type'] ?? 'expense') === 'income' ? 'income' : 'expense';
        $allowed = ['paid','pending','overdue'];
        $status = in_array($_POST['status'] ?? '', $allowed, true) ? $_POST['status'] : 'paid';
        $data = [
            $_POST['txn_date'] ?: date('Y-m-d'), trim($_POST['description'] ?? ''), trim($_POST['category'] ?? ''),
            $type, (float)($_POST['amount'] ?? 0), $status, ((int)($_POST['project_id'] ?? 0)) ?: null,
        ];
        if ($id > 0) {
            $db->prepare("UPDATE transactions SET txn_date=?,description=?,category=?,type=?,amount=?,status=?,project_id=? WHERE id=?")->execute([...$data,$id]);
            flash('Transaction updated.');
        } else {
            $db->prepare("INSERT INTO transactions (txn_date,description,category,type,amount,status,project_id) VALUES (?,?,?,?,?,?,?)")->execute($data);
            flash('Transaction added.');
        }
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM transactions WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Transaction deleted.', 'info');
    }
    redirect('finance.php');
}

$projects = $db->query("SELECT id,name FROM projects ORDER BY name")->fetchAll();
$projOpts = ['' => '— none —']; foreach ($projects as $p) $projOpts[$p['id']] = $p['name'];

$action = $_GET['action'] ?? 'list';
$edit = null;
if ($action === 'form' && !empty($_GET['id'])) { $st=$db->prepare("SELECT * FROM transactions WHERE id=?");$st->execute([(int)$_GET['id']]);$edit=$st->fetch()?:null; }

$page = 'finance';
if ($action === 'form') {
    $page_title = $edit ? 'Edit Transaction' : 'New Transaction';
    require __DIR__ . '/inc/header.php'; ?>
    <div class="p-6"><div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <form method="post" class="space-y-4">
        <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?= field_input('Description','description',$edit['description']??'','text','Client Payment - Phase 3') ?>
          <?= field_input('Category','category',$edit['category']??'','text','Client Receipt') ?>
          <?= field_select('Type','type',['income'=>'Income','expense'=>'Expense'],$edit['type']??'expense') ?>
          <?= field_input('Amount ('.APP_CURRENCY.')','amount',$edit['amount']??'','number','0','step="0.01"') ?>
          <?= field_select('Status','status',['paid'=>'Paid','pending'=>'Pending','overdue'=>'Overdue'],$edit['status']??'paid') ?>
          <?= field_select('Project','project_id',$projOpts,$edit['project_id']??'') ?>
          <?= field_input('Date','txn_date',$edit['txn_date']??date('Y-m-d'),'date') ?>
        </div>
        <div class="flex gap-3 pt-2">
          <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl"><?= $edit?'Save changes':'Add transaction' ?></button>
          <a href="finance.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
        </div>
      </form>
    </div></div>
    <?php require __DIR__ . '/inc/footer.php'; exit;
}

$income  = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND status='paid'")->fetchColumn();
$expense = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();
$pending = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status IN('pending','overdue')")->fetchColumn();
$rows = $db->query("SELECT t.*, p.name AS project_name FROM transactions t LEFT JOIN projects p ON p.id=t.project_id ORDER BY t.txn_date DESC, t.id DESC")->fetchAll();
$sbadge = ['paid'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-amber-100 text-amber-700','overdue'=>'bg-rose-100 text-rose-600'];

$page_title = 'Finance';
$action = ['label'=>'New Transaction','href'=>'finance.php?action=form'];
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 space-y-4">
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3"><?= icon('trending-up',18) ?></div>
      <p class="text-2xl font-bold text-slate-900 font-display"><?= money($income) ?></p><p class="text-xs text-slate-500">Income received</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center mb-3"><?= icon('trending-down',18) ?></div>
      <p class="text-2xl font-bold text-slate-900 font-display"><?= money($expense) ?></p><p class="text-xs text-slate-500">Total expenses</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3"><?= icon('alert-circle',18) ?></div>
      <p class="text-2xl font-bold text-slate-900 font-display"><?= money($pending) ?></p><p class="text-xs text-slate-500">Pending / overdue</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 font-semibold">Date</th>
          <th class="px-5 py-3 font-semibold">Description</th>
          <th class="px-5 py-3 font-semibold hidden lg:table-cell">Project</th>
          <th class="px-5 py-3 font-semibold text-center">Status</th>
          <th class="px-5 py-3 font-semibold text-right">Amount</th>
          <th class="px-5 py-3 font-semibold text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($rows as $t): $inc = $t['type']==='income'; ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3 text-slate-500 whitespace-nowrap"><?= e($t['txn_date']) ?></td>
            <td class="px-5 py-3"><p class="font-medium text-slate-800"><?= e($t['description']) ?></p><p class="text-xs text-slate-400"><?= e($t['category']) ?></p></td>
            <td class="px-5 py-3 hidden lg:table-cell text-slate-500"><?= e($t['project_name'] ?: '—') ?></td>
            <td class="px-5 py-3 text-center"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg <?= $sbadge[$t['status']]??'' ?>"><?= ucfirst($t['status']) ?></span></td>
            <td class="px-5 py-3 text-right font-bold whitespace-nowrap <?= $inc?'text-emerald-600':'text-rose-500' ?>"><?= $inc?'+':'-' ?><?= money($t['amount']) ?></td>
            <td class="px-5 py-3"><div class="flex items-center justify-end gap-1.5">
              <a href="finance.php?action=form&id=<?= $t['id'] ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center"><?= icon('edit',15) ?></a>
              <form method="post" onsubmit="return confirm('Delete this transaction?')"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
              </form>
            </div></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No transactions yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
