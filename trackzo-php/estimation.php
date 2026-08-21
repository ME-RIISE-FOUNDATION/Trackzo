<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'add') {
        $db->prepare("INSERT INTO estimation_items (description,unit,qty,rate,tax,discount) VALUES (?,?,?,?,?,?)")
           ->execute([trim($_POST['description'] ?? 'New item'), trim($_POST['unit'] ?? ''), (float)($_POST['qty'] ?? 0), (float)($_POST['rate'] ?? 0), (float)($_POST['tax'] ?? 0), (float)($_POST['discount'] ?? 0)]);
        flash('Line item added.');
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM estimation_items WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Line item removed.', 'info');
    }
    redirect('estimation.php');
}

$rows = $db->query("SELECT * FROM estimation_items ORDER BY id")->fetchAll();
$grand = 0; $subTotal = 0; $taxTotal = 0; $discTotal = 0;
foreach ($rows as &$r) {
    $base = (float)$r['qty'] * (float)$r['rate'];
    $disc = $base * (float)$r['discount'] / 100;
    $taxable = $base - $disc;
    $tax = $taxable * (float)$r['tax'] / 100;
    $r['_line'] = $taxable + $tax;
    $subTotal += $base; $discTotal += $disc; $taxTotal += $tax; $grand += $r['_line'];
}
unset($r);

$page = 'estimation'; $page_title = 'Estimation';
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 space-y-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 font-semibold">Description</th>
          <th class="px-5 py-3 font-semibold hidden md:table-cell">Unit</th>
          <th class="px-5 py-3 font-semibold text-right">Qty</th>
          <th class="px-5 py-3 font-semibold text-right">Rate</th>
          <th class="px-5 py-3 font-semibold text-right hidden sm:table-cell">Tax %</th>
          <th class="px-5 py-3 font-semibold text-right hidden sm:table-cell">Disc %</th>
          <th class="px-5 py-3 font-semibold text-right">Line Total</th>
          <th class="px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($rows as $r): ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3 font-medium text-slate-800"><?= e($r['description']) ?></td>
            <td class="px-5 py-3 hidden md:table-cell text-slate-500"><?= e($r['unit']) ?></td>
            <td class="px-5 py-3 text-right text-slate-600"><?= rtrim(rtrim(number_format($r['qty'],2),'0'),'.') ?></td>
            <td class="px-5 py-3 text-right text-slate-600"><?= money($r['rate']) ?></td>
            <td class="px-5 py-3 text-right hidden sm:table-cell text-slate-500"><?= rtrim(rtrim(number_format($r['tax'],2),'0'),'.') ?></td>
            <td class="px-5 py-3 text-right hidden sm:table-cell text-slate-500"><?= rtrim(rtrim(number_format($r['discount'],2),'0'),'.') ?></td>
            <td class="px-5 py-3 text-right font-semibold text-slate-800"><?= money($r['_line']) ?></td>
            <td class="px-5 py-3 text-right">
              <form method="post" onsubmit="return confirm('Remove line item?')"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="8" class="px-5 py-8 text-center text-slate-400">No line items yet. Add your first below.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add line item -->
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <p class="text-sm font-bold text-slate-800 font-display mb-3">Add line item</p>
    <form method="post" class="grid grid-cols-2 md:grid-cols-7 gap-3 items-end">
      <?= csrf_field() ?><input type="hidden" name="op" value="add">
      <div class="col-span-2 md:col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1.5">Description</label><input name="description" required placeholder="Excavation work" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50"></div>
      <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Unit</label><input name="unit" placeholder="Cu.Yd" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50"></div>
      <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Qty</label><input name="qty" type="number" step="0.01" value="1" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 text-right"></div>
      <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Rate</label><input name="rate" type="number" step="0.01" value="0" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 text-right"></div>
      <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Tax %</label><input name="tax" type="number" step="0.01" value="8" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 text-right"></div>
      <div class="flex gap-2">
        <div class="flex-1"><label class="block text-xs font-semibold text-slate-600 mb-1.5">Disc %</label><input name="discount" type="number" step="0.01" value="0" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 text-right"></div>
      </div>
      <div class="col-span-2 md:col-span-7"><button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('plus',16) ?>Add item</button></div>
    </form>
  </div>

  <div class="flex justify-end">
    <div class="w-full sm:w-80 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-2 text-sm">
      <div class="flex justify-between text-slate-500"><span>Subtotal</span><span class="font-medium text-slate-700"><?= money($subTotal) ?></span></div>
      <div class="flex justify-between text-slate-500"><span>Discount</span><span class="font-medium text-rose-500">- <?= money($discTotal) ?></span></div>
      <div class="flex justify-between text-slate-500"><span>Tax</span><span class="font-medium text-slate-700">+ <?= money($taxTotal) ?></span></div>
      <div class="flex justify-between pt-2 border-t border-slate-100 text-base"><span class="font-bold text-slate-800">Grand Total</span><span class="font-bold text-brand font-display"><?= money($grand) ?></span></div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
