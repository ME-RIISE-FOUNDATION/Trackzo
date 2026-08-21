<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

// ---- Handle actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            trim($_POST['name'] ?? ''), trim($_POST['company'] ?? ''), trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''),
            ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
            $_POST['joined_date'] ?: null,
        ];
        if ($id > 0) {
            $st = $db->prepare("UPDATE clients SET name=?,company=?,email=?,phone=?,address=?,city=?,status=?,joined_date=? WHERE id=?");
            $st->execute([...$data, $id]);
            flash('Client updated.');
        } else {
            $st = $db->prepare("INSERT INTO clients (name,company,email,phone,address,city,status,joined_date) VALUES (?,?,?,?,?,?,?,?)");
            $st->execute($data);
            flash('Client added.');
        }
    } elseif ($op === 'delete') {
        $st = $db->prepare("DELETE FROM clients WHERE id=?");
        $st->execute([(int) $_POST['id']]);
        flash('Client deleted.', 'info');
    }
    redirect('clients.php');
}

$action = $_GET['action'] ?? 'list';
$edit = null;
if ($action === 'form' && !empty($_GET['id'])) {
    $st = $db->prepare("SELECT * FROM clients WHERE id=?");
    $st->execute([(int) $_GET['id']]);
    $edit = $st->fetch() ?: null;
}

$page = 'clients';
if ($action === 'form') {
    $page_title = $edit ? 'Edit Client' : 'New Client';
    require __DIR__ . '/inc/header.php';
    ?>
    <div class="p-6">
      <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="post" class="space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="op" value="save">
          <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php
            function field($label,$name,$val,$type='text',$ph=''){
              echo '<div><label class="block text-xs font-semibold text-slate-600 mb-1.5">'.e($label).'</label>';
              echo '<input type="'.$type.'" name="'.$name.'" value="'.e($val).'" placeholder="'.e($ph).'" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400"></div>';
            }
            field('Contact name','name',$edit['name']??'','text','Jane Doe');
            field('Company','company',$edit['company']??'','text','Acme Ltd.');
            field('Email','email',$edit['email']??'','email','jane@acme.com');
            field('Phone','phone',$edit['phone']??'','text','+1 555-0100');
            field('Address','address',$edit['address']??'','text','88 Wall Street');
            field('City','city',$edit['city']??'','text','New York, NY');
            field('Joined date','joined_date',$edit['joined_date']??date('Y-m-d'),'date');
            ?>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
              <select name="status" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="active" <?= ($edit['status']??'active')==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= ($edit['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
              </select>
            </div>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl"><?= $edit?'Save changes':'Add client' ?></button>
            <a href="clients.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    <?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

// ---- List ----
$rows = $db->query("
  SELECT c.*,
    (SELECT COUNT(*) FROM projects p WHERE p.client_id=c.id) AS proj_count,
    (SELECT COALESCE(SUM(p.budget),0) FROM projects p WHERE p.client_id=c.id) AS total_value
  FROM clients c ORDER BY c.name")->fetchAll();

$page_title = 'Clients';
$action_btn = ['label'=>'Add Client','href'=>'clients.php?action=form'];
$action = $action_btn;
require __DIR__ . '/inc/header.php';
?>
<div class="p-6">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
            <th class="px-5 py-3 font-semibold">Client</th>
            <th class="px-5 py-3 font-semibold hidden md:table-cell">Contact</th>
            <th class="px-5 py-3 font-semibold hidden lg:table-cell">City</th>
            <th class="px-5 py-3 font-semibold text-center">Projects</th>
            <th class="px-5 py-3 font-semibold text-right">Total Value</th>
            <th class="px-5 py-3 font-semibold text-center">Status</th>
            <th class="px-5 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($rows as $c):
            $parts = preg_split('/\s+/', trim($c['name']));
            $ini = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):'')); ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= e($ini) ?></div>
                <div class="min-w-0">
                  <p class="font-semibold text-slate-800 truncate"><?= e($c['name']) ?></p>
                  <p class="text-xs text-slate-400 truncate"><?= e($c['company']) ?></p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 hidden md:table-cell">
              <p class="text-slate-600 truncate"><?= e($c['email']) ?></p>
              <p class="text-xs text-slate-400"><?= e($c['phone']) ?></p>
            </td>
            <td class="px-5 py-3 hidden lg:table-cell text-slate-500"><?= e($c['city']) ?></td>
            <td class="px-5 py-3 text-center text-slate-700 font-semibold"><?= (int)$c['proj_count'] ?></td>
            <td class="px-5 py-3 text-right font-semibold text-slate-800"><?= money($c['total_value']) ?></td>
            <td class="px-5 py-3 text-center">
              <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg <?= $c['status']==='active'?'bg-emerald-100 text-emerald-700':'bg-slate-100 text-slate-500' ?>"><?= ucfirst($c['status']) ?></span>
            </td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-1.5">
                <a href="clients.php?action=form&id=<?= $c['id'] ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center" title="Edit"><?= icon('edit',15) ?></a>
                <form method="post" onsubmit="return confirm('Delete this client?')">
                  <?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center" title="Delete"><?= icon('trash',15) ?></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No clients yet. Click <strong>Add Client</strong> to create one.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
