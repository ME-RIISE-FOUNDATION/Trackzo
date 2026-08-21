<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $allowed = ['planning','active','on-hold','completed'];
        $status = in_array($_POST['status'] ?? '', $allowed, true) ? $_POST['status'] : 'planning';
        $data = [
            trim($_POST['name'] ?? ''), ((int)($_POST['client_id'] ?? 0)) ?: null, trim($_POST['site_address'] ?? ''),
            trim($_POST['type'] ?? ''), $status, (float)($_POST['budget'] ?? 0), (float)($_POST['spent'] ?? 0),
            max(0, min(100, (int)($_POST['progress'] ?? 0))), $_POST['start_date'] ?: null, $_POST['end_date'] ?: null,
            (int)($_POST['area'] ?? 0), (int)($_POST['floors'] ?? 0), trim($_POST['manager'] ?? ''), trim($_POST['description'] ?? ''),
        ];
        if ($id > 0) {
            $st = $db->prepare("UPDATE projects SET name=?,client_id=?,site_address=?,type=?,status=?,budget=?,spent=?,progress=?,start_date=?,end_date=?,area=?,floors=?,manager=?,description=? WHERE id=?");
            $st->execute([...$data, $id]); flash('Project updated.');
        } else {
            $st = $db->prepare("INSERT INTO projects (name,client_id,site_address,type,status,budget,spent,progress,start_date,end_date,area,floors,manager,description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $st->execute($data); flash('Project created.');
        }
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM projects WHERE id=?")->execute([(int) $_POST['id']]);
        flash('Project deleted.', 'info');
    }
    redirect('projects.php');
}

$clients = $db->query("SELECT id,name,company FROM clients ORDER BY name")->fetchAll();
$clientOpts = ['' => '— none —'];
foreach ($clients as $c) $clientOpts[$c['id']] = $c['company'] ? $c['company'] . ' (' . $c['name'] . ')' : $c['name'];

$action = $_GET['action'] ?? 'list';
$edit = null;
if ($action === 'form' && !empty($_GET['id'])) {
    $st = $db->prepare("SELECT * FROM projects WHERE id=?"); $st->execute([(int)$_GET['id']]); $edit = $st->fetch() ?: null;
}

$page = 'projects';
if ($action === 'form') {
    $page_title = $edit ? 'Edit Project' : 'New Project';
    require __DIR__ . '/inc/header.php'; ?>
    <div class="p-6">
      <div class="max-w-3xl mx-auto bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="post" class="space-y-4">
          <?= csrf_field() ?><input type="hidden" name="op" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?= field_input('Project name','name',$edit['name']??'','text','Skyline Tower') ?>
            <?= field_select('Client','client_id',$clientOpts,$edit['client_id']??'') ?>
            <?= field_input('Type','type',$edit['type']??'','text','Residential High-Rise') ?>
            <?= field_select('Status','status',['planning'=>'Planning','active'=>'Active','on-hold'=>'On Hold','completed'=>'Completed'],$edit['status']??'planning') ?>
            <?= field_input('Budget ('.APP_CURRENCY.')','budget',$edit['budget']??'','number','0','step="0.01"') ?>
            <?= field_input('Spent ('.APP_CURRENCY.')','spent',$edit['spent']??'','number','0','step="0.01"') ?>
            <?= field_input('Progress (%)','progress',$edit['progress']??0,'number','0','min="0" max="100"') ?>
            <?= field_input('Manager','manager',$edit['manager']??'','text','James Carter') ?>
            <?= field_input('Start date','start_date',$edit['start_date']??'','date') ?>
            <?= field_input('End date','end_date',$edit['end_date']??'','date') ?>
            <?= field_input('Area (sq.ft)','area',$edit['area']??'','number') ?>
            <?= field_input('Floors','floors',$edit['floors']??'','number') ?>
            <?= field_input('Site address','site_address',$edit['site_address']??'','text','14 Harbor Blvd, NY') ?>
            <?= field_textarea('Description','description',$edit['description']??'') ?>
          </div>
          <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl"><?= $edit?'Save changes':'Create project' ?></button>
            <a href="projects.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    <?php require __DIR__ . '/inc/footer.php'; exit;
}

$rows = $db->query("SELECT p.*, c.company AS client_company, c.name AS client_name FROM projects p LEFT JOIN clients c ON c.id=p.client_id ORDER BY p.id DESC")->fetchAll();
$badge = ['active'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','on-hold'=>'bg-amber-100 text-amber-700','planning'=>'bg-slate-100 text-slate-600'];

$page_title = 'Projects';
$action = ['label'=>'New Project','href'=>'projects.php?action=form'];
require __DIR__ . '/inc/header.php';
?>
<div class="p-6">
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <?php foreach ($rows as $p): $rem = (float)$p['budget'] - (float)$p['spent']; ?>
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0"><?= icon('folder',18) ?></div>
          <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg <?= $badge[$p['status']]??'bg-slate-100 text-slate-600' ?>"><?= ucfirst(str_replace('-',' ',$p['status'])) ?></span>
        </div>
        <h3 class="font-bold text-slate-800 font-display leading-tight"><?= e($p['name']) ?></h3>
        <p class="text-xs text-slate-400 mb-1"><?= e($p['client_company'] ?: $p['client_name'] ?: 'No client') ?></p>
        <p class="text-xs text-slate-400 flex items-center gap-1 mb-3"><?= icon('map-pin',12) ?><span class="truncate"><?= e($p['site_address']) ?></span></p>
        <div class="flex items-center gap-2 mb-3">
          <div class="flex-1 bg-slate-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-blue-500" style="width:<?= (int)$p['progress'] ?>%"></div></div>
          <span class="text-[11px] text-slate-500"><?= (int)$p['progress'] ?>%</span>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center mb-4">
          <div><p class="text-[10px] text-slate-400 uppercase tracking-wide">Budget</p><p class="text-sm font-bold text-slate-800"><?= money_short($p['budget']) ?></p></div>
          <div><p class="text-[10px] text-slate-400 uppercase tracking-wide">Spent</p><p class="text-sm font-bold text-rose-500"><?= money_short($p['spent']) ?></p></div>
          <div><p class="text-[10px] text-slate-400 uppercase tracking-wide">Left</p><p class="text-sm font-bold text-emerald-600"><?= money_short($rem) ?></p></div>
        </div>
        <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
          <a href="projects.php?action=form&id=<?= $p['id'] ?>" class="flex-1 text-center py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this project?')">
            <?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="w-9 h-9 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="col-span-full bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">No projects yet. Click <strong>New Project</strong> to add one.</div><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
