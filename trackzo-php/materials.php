<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            trim($_POST['name'] ?? ''), trim($_POST['category'] ?? ''), trim($_POST['unit'] ?? ''),
            (float)($_POST['stock'] ?? 0), (float)($_POST['min_stock'] ?? 0), (float)($_POST['rate'] ?? 0),
            trim($_POST['supplier'] ?? ''), date('Y-m-d'),
        ];
        if ($id > 0) {
            $db->prepare("UPDATE materials SET name=?,category=?,unit=?,stock=?,min_stock=?,rate=?,supplier=?,last_updated=? WHERE id=?")->execute([...$data,$id]);
            flash('Material updated.');
        } else {
            $db->prepare("INSERT INTO materials (name,category,unit,stock,min_stock,rate,supplier,last_updated) VALUES (?,?,?,?,?,?,?,?)")->execute($data);
            flash('Material added.');
        }
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM materials WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Material deleted.', 'info');
    }
    redirect('materials.php');
}

$action = $_GET['action'] ?? 'list';
$edit = null;
if ($action === 'form' && !empty($_GET['id'])) { $st=$db->prepare("SELECT * FROM materials WHERE id=?");$st->execute([(int)$_GET['id']]);$edit=$st->fetch()?:null; }

$page = 'materials';
if ($action === 'form') {
    $page_title = $edit ? 'Edit Material' : 'New Material';
    require __DIR__ . '/inc/header.php'; ?>
    <div class="p-6"><div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <form method="post" class="space-y-4">
        <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?= field_input('Material name','name',$edit['name']??'','text','Portland Cement') ?>
          <?= field_input('Category','category',$edit['category']??'','text','Cement') ?>
          <?= field_input('Unit','unit',$edit['unit']??'','text','Bags (50kg)') ?>
          <?= field_input('Supplier','supplier',$edit['supplier']??'','text','Atlas Cement Co.') ?>
          <?= field_input('Stock','stock',$edit['stock']??'','number','0','step="0.01"') ?>
          <?= field_input('Min. stock','min_stock',$edit['min_stock']??'','number','0','step="0.01"') ?>
          <?= field_input('Rate ('.APP_CURRENCY.')','rate',$edit['rate']??'','number','0','step="0.01"') ?>
        </div>
        <div class="flex gap-3 pt-2">
          <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl"><?= $edit?'Save changes':'Add material' ?></button>
          <a href="materials.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
        </div>
      </form>
    </div></div>
    <?php require __DIR__ . '/inc/footer.php'; exit;
}

$rows = $db->query("SELECT * FROM materials ORDER BY name")->fetchAll();
$lowCount = 0; foreach ($rows as $r) if ((float)$r['stock'] < (float)$r['min_stock']) $lowCount++;

$page_title = 'Materials & Inventory';
$action = ['label'=>'Add Material','href'=>'materials.php?action=form'];
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 space-y-4">
  <?php if ($lowCount): ?>
    <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm font-medium flex items-center gap-2"><?= icon('alert-circle',16) ?><?= $lowCount ?> item(s) are below minimum stock level.</div>
  <?php endif; ?>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 font-semibold">Material</th>
          <th class="px-5 py-3 font-semibold hidden md:table-cell">Category</th>
          <th class="px-5 py-3 font-semibold hidden lg:table-cell">Supplier</th>
          <th class="px-5 py-3 font-semibold text-right">Stock</th>
          <th class="px-5 py-3 font-semibold text-right">Rate</th>
          <th class="px-5 py-3 font-semibold text-right">Value</th>
          <th class="px-5 py-3 font-semibold text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($rows as $m): $low = (float)$m['stock'] < (float)$m['min_stock']; ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3">
              <p class="font-semibold text-slate-800"><?= e($m['name']) ?></p>
              <p class="text-xs text-slate-400"><?= e($m['unit']) ?></p>
            </td>
            <td class="px-5 py-3 hidden md:table-cell"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600"><?= e($m['category']) ?></span></td>
            <td class="px-5 py-3 hidden lg:table-cell text-slate-500"><?= e($m['supplier']) ?></td>
            <td class="px-5 py-3 text-right">
              <span class="font-semibold <?= $low?'text-rose-600':'text-slate-800' ?>"><?= rtrim(rtrim(number_format($m['stock'],2),'0'),'.') ?></span>
              <?php if ($low): ?><span class="block text-[10px] text-rose-500">Low (min <?= rtrim(rtrim(number_format($m['min_stock'],2),'0'),'.') ?>)</span><?php endif; ?>
            </td>
            <td class="px-5 py-3 text-right text-slate-600"><?= money($m['rate']) ?></td>
            <td class="px-5 py-3 text-right font-semibold text-slate-800"><?= money($m['stock']*$m['rate']) ?></td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-1.5">
                <a href="materials.php?action=form&id=<?= $m['id'] ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center"><?= icon('edit',15) ?></a>
                <form method="post" onsubmit="return confirm('Delete this material?')"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $m['id'] ?>">
                  <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No materials yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
