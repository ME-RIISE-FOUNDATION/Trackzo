<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $qty = (float)($_POST['qty'] ?? 0); $rate = (float)($_POST['rate'] ?? 0);
        $allowed = ['pending','approved','delivered','cancelled'];
        $status = in_array($_POST['status'] ?? '', $allowed, true) ? $_POST['status'] : 'pending';
        $data = [
            trim($_POST['supplier'] ?? ''), trim($_POST['item'] ?? ''), $qty, $rate, $qty*$rate,
            $status, $_POST['order_date'] ?: null, $_POST['expected_date'] ?: null, ((int)($_POST['project_id'] ?? 0)) ?: null,
        ];
        if ($id > 0) {
            $db->prepare("UPDATE purchase_orders SET supplier=?,item=?,qty=?,rate=?,total=?,status=?,order_date=?,expected_date=?,project_id=? WHERE id=?")->execute([...$data,$id]);
            flash('Purchase order updated.');
        } else {
            $db->prepare("INSERT INTO purchase_orders (supplier,item,qty,rate,total,status,order_date,expected_date,project_id) VALUES (?,?,?,?,?,?,?,?,?)")->execute($data);
            flash('Purchase order created.');
        }
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM purchase_orders WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Purchase order deleted.', 'info');
    }
    redirect('purchase.php');
}

$projects = $db->query("SELECT id,name FROM projects ORDER BY name")->fetchAll();
$projOpts = ['' => '— none —']; foreach ($projects as $p) $projOpts[$p['id']] = $p['name'];

$action = $_GET['action'] ?? 'list';
$edit = null;
if ($action === 'form' && !empty($_GET['id'])) { $st=$db->prepare("SELECT * FROM purchase_orders WHERE id=?");$st->execute([(int)$_GET['id']]);$edit=$st->fetch()?:null; }

$page = 'purchase';
if ($action === 'form') {
    $page_title = $edit ? 'Edit Purchase Order' : 'New Purchase Order';
    require __DIR__ . '/inc/header.php'; ?>
    <div class="p-6"><div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <form method="post" class="space-y-4">
        <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?= field_input('Supplier','supplier',$edit['supplier']??'','text','Atlas Cement Co.') ?>
          <?= field_input('Item','item',$edit['item']??'','text','Portland Cement OPC 53') ?>
          <?= field_input('Quantity','qty',$edit['qty']??'','number','0','step="0.01"') ?>
          <?= field_input('Rate ('.APP_CURRENCY.')','rate',$edit['rate']??'','number','0','step="0.01"') ?>
          <?= field_select('Status','status',['pending'=>'Pending','approved'=>'Approved','delivered'=>'Delivered','cancelled'=>'Cancelled'],$edit['status']??'pending') ?>
          <?= field_select('Project','project_id',$projOpts,$edit['project_id']??'') ?>
          <?= field_input('Order date','order_date',$edit['order_date']??date('Y-m-d'),'date') ?>
          <?= field_input('Expected date','expected_date',$edit['expected_date']??'','date') ?>
        </div>
        <div class="flex gap-3 pt-2">
          <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl"><?= $edit?'Save changes':'Create order' ?></button>
          <a href="purchase.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
        </div>
      </form>
    </div></div>
    <?php require __DIR__ . '/inc/footer.php'; exit;
}

$rows = $db->query("SELECT po.*, p.name AS project_name FROM purchase_orders po LEFT JOIN projects p ON p.id=po.project_id ORDER BY po.order_date DESC, po.id DESC")->fetchAll();
$badge = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-blue-100 text-blue-700','delivered'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-rose-100 text-rose-600'];

$page_title = 'Purchase Orders';
$action = ['label'=>'New Order','href'=>'purchase.php?action=form'];
require __DIR__ . '/inc/header.php';
?>
<div class="p-6">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 font-semibold">Supplier / Item</th>
          <th class="px-5 py-3 font-semibold hidden lg:table-cell">Project</th>
          <th class="px-5 py-3 font-semibold text-right">Qty × Rate</th>
          <th class="px-5 py-3 font-semibold text-right">Total</th>
          <th class="px-5 py-3 font-semibold hidden md:table-cell">Expected</th>
          <th class="px-5 py-3 font-semibold text-center">Status</th>
          <th class="px-5 py-3 font-semibold text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($rows as $o): ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3"><p class="font-semibold text-slate-800"><?= e($o['supplier']) ?></p><p class="text-xs text-slate-400"><?= e($o['item']) ?></p></td>
            <td class="px-5 py-3 hidden lg:table-cell text-slate-500"><?= e($o['project_name'] ?: '—') ?></td>
            <td class="px-5 py-3 text-right text-slate-600"><?= rtrim(rtrim(number_format($o['qty'],2),'0'),'.') ?> × <?= money($o['rate']) ?></td>
            <td class="px-5 py-3 text-right font-semibold text-slate-800"><?= money($o['total']) ?></td>
            <td class="px-5 py-3 hidden md:table-cell text-slate-500"><?= e($o['expected_date'] ?: '—') ?></td>
            <td class="px-5 py-3 text-center"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg <?= $badge[$o['status']]??'bg-slate-100 text-slate-600' ?>"><?= ucfirst($o['status']) ?></span></td>
            <td class="px-5 py-3"><div class="flex items-center justify-end gap-1.5">
              <a href="purchase.php?action=form&id=<?= $o['id'] ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center"><?= icon('edit',15) ?></a>
              <form method="post" onsubmit="return confirm('Delete this order?')"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $o['id'] ?>">
                <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
              </form>
            </div></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No purchase orders yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
