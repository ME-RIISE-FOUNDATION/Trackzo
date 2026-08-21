<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $allowed = ['bank','cash','credit'];
        $type = in_array($_POST['type'] ?? '', $allowed, true) ? $_POST['type'] : 'bank';
        $data = [trim($_POST['name'] ?? ''), $type, (float)($_POST['balance'] ?? 0), trim($_POST['currency'] ?? 'USD'), date('Y-m-d')];
        if ($id > 0) {
            $db->prepare("UPDATE accounts SET name=?,type=?,balance=?,currency=?,last_transaction=? WHERE id=?")->execute([...$data,$id]);
            flash('Account updated.');
        } else {
            $db->prepare("INSERT INTO accounts (name,type,balance,currency,last_transaction) VALUES (?,?,?,?,?)")->execute($data);
            flash('Account added.');
        }
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM accounts WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Account deleted.', 'info');
    }
    redirect('account_tracker.php');
}

$action = $_GET['action'] ?? 'list';
$edit = null;
if ($action === 'form' && !empty($_GET['id'])) { $st=$db->prepare("SELECT * FROM accounts WHERE id=?");$st->execute([(int)$_GET['id']]);$edit=$st->fetch()?:null; }

$page = 'account-tracker';
if ($action === 'form') {
    $page_title = $edit ? 'Edit Account' : 'New Account';
    require __DIR__ . '/inc/header.php'; ?>
    <div class="p-6"><div class="max-w-xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <form method="post" class="space-y-4">
        <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?= field_input('Account name','name',$edit['name']??'','text','Chase Business Checking') ?>
          <?= field_select('Type','type',['bank'=>'Bank','cash'=>'Cash','credit'=>'Credit'],$edit['type']??'bank') ?>
          <?= field_input('Balance','balance',$edit['balance']??'','number','0','step="0.01"') ?>
          <?= field_input('Currency','currency',$edit['currency']??'USD','text','USD') ?>
        </div>
        <div class="flex gap-3 pt-2">
          <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl"><?= $edit?'Save changes':'Add account' ?></button>
          <a href="account_tracker.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
        </div>
      </form>
    </div></div>
    <?php require __DIR__ . '/inc/footer.php'; exit;
}

$rows = $db->query("SELECT * FROM accounts ORDER BY id")->fetchAll();
$net = 0; foreach ($rows as $r) $net += (float)$r['balance'];
$typeMeta = ['bank'=>['dollar','bg-blue-50 text-blue-600'],'cash'=>['dollar','bg-emerald-50 text-emerald-600'],'credit'=>['credit-card','bg-violet-50 text-violet-600']];

$page_title = 'Account Tracker';
$action = ['label'=>'Add Account','href'=>'account_tracker.php?action=form'];
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 space-y-4">
  <div class="rounded-2xl p-5 text-white" style="background:linear-gradient(135deg,#0F2B4C 0%,#1D4ED8 100%)">
    <p class="text-sm font-semibold text-blue-200 mb-1">Total Net Balance</p>
    <p class="text-3xl font-bold font-display"><?= money($net) ?></p>
    <p class="text-xs text-blue-200 mt-1"><?= count($rows) ?> account(s) tracked</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($rows as $a): $m = $typeMeta[$a['type']] ?? $typeMeta['bank']; $neg=(float)$a['balance']<0; ?>
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-4">
          <div class="w-10 h-10 rounded-xl <?= $m[1] ?> flex items-center justify-center"><?= icon($m[0],18) ?></div>
          <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 uppercase"><?= e($a['type']) ?></span>
        </div>
        <p class="text-sm text-slate-500 truncate"><?= e($a['name']) ?></p>
        <p class="text-2xl font-bold font-display <?= $neg?'text-rose-500':'text-slate-900' ?>"><?= money($a['balance']) ?></p>
        <p class="text-xs text-slate-400 mt-1"><?= e($a['currency']) ?> · updated <?= e($a['last_transaction']) ?></p>
        <div class="flex items-center gap-2 pt-4 mt-4 border-t border-slate-100">
          <a href="account_tracker.php?action=form&id=<?= $a['id'] ?>" class="flex-1 text-center py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this account?')"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $a['id'] ?>">
            <button class="w-9 h-9 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="col-span-full bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">No accounts yet.</div><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
