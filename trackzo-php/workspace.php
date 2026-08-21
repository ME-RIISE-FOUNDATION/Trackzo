<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

/* Section catalogue: slug => [label, icon] */
$SECTIONS = [
    'overview'     => ['Overview', 'dashboard'],
    'customer'     => ['Customer Profile', 'user'],
    'owner'        => ['Owner Details', 'users'],
    'site'         => ['Site Address', 'map-pin'],
    'measurements' => ['Property Measurements', 'ruler'],
    'construction' => ['Construction Details', 'tool'],
    'materials'    => ['Material Management', 'package'],
    'estimation'   => ['Cost Estimation', 'clipboard'],
    'expenses'     => ['Expense Tracker', 'wallet'],
    'progress'     => ['Construction Progress', 'activity'],
    'documents'    => ['Documents', 'file-text'],
    'reports'      => ['Reports', 'bar-chart'],
    'notes'        => ['Notes', 'sticky-note'],
];

/* Which project_details columns each profile section owns */
$DETAIL_COLS = [
    'customer'     => ['customer_name', 'customer_company', 'customer_email', 'customer_phone'],
    'owner'        => ['owner_name', 'owner_email', 'owner_phone', 'owner_address'],
    'site'         => ['site_address', 'site_city', 'site_state', 'site_pincode', 'site_maplink'],
    'measurements' => ['plot_length', 'plot_width', 'plot_area', 'builtup_sqft'],
    'construction' => ['construction_type', 'structure_type', 'foundation_type', 'roofing_type', 'num_floors', 'num_units', 'construction_notes'],
];
$NUMERIC_DETAIL = ['plot_length', 'plot_width', 'plot_area', 'builtup_sqft', 'num_floors', 'num_units'];

function ensure_details(PDO $db, int $pid): void
{
    $db->prepare("INSERT IGNORE INTO project_details (project_id) VALUES (?)")->execute([$pid]);
}

/* ============================ POST ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $entity  = $_POST['entity'] ?? '';
    $pid     = (int) ($_POST['project_id'] ?? 0);
    $section = $_POST['section'] ?? 'overview';
    $back    = 'workspace.php?project=' . $pid . '&section=' . $section;

    if ($entity === 'project_new') {
        $allowed = ['planning','active','on-hold','completed'];
        $status = in_array($_POST['status'] ?? '', $allowed, true) ? $_POST['status'] : 'planning';
        $st = $db->prepare("INSERT INTO projects (name,type,status,budget,start_date,manager) VALUES (?,?,?,?,?,?)");
        $st->execute([
            trim($_POST['name'] ?? 'Untitled Project'), trim($_POST['type'] ?? ''), $status,
            (float) ($_POST['budget'] ?? 0), $_POST['start_date'] ?: null, trim($_POST['manager'] ?? ''),
        ]);
        $newId = (int) $db->lastInsertId();
        ensure_details($db, $newId);
        flash('Project created.');
        redirect('workspace.php?project=' . $newId . '&section=overview');
    }

    if ($pid <= 0) redirect('workspace.php');

    switch ($entity) {
        case 'details':
            $cols = $DETAIL_COLS[$section] ?? [];
            if ($cols) {
                ensure_details($db, $pid);
                $set = []; $vals = [];
                foreach ($cols as $c) {
                    $v = $_POST[$c] ?? '';
                    if (in_array($c, $GLOBALS['NUMERIC_DETAIL'], true)) $v = ($v === '' ? null : $v);
                    $set[] = "$c=?"; $vals[] = $v;
                }
                $vals[] = $pid;
                $db->prepare("UPDATE project_details SET " . implode(',', $set) . " WHERE project_id=?")->execute($vals);
                flash('Details saved.');
            }
            break;

        case 'material':
            if (($_POST['op'] ?? '') === 'delete') {
                $db->prepare("DELETE FROM project_materials WHERE id=? AND project_id=?")->execute([(int)$_POST['id'], $pid]);
                flash('Material deleted.', 'info');
            } else {
                $qty = (float)($_POST['quantity'] ?? 0); $cost = (float)($_POST['cost'] ?? 0);
                $vals = [$pid, trim($_POST['name'] ?? ''), trim($_POST['category'] ?? ''), $qty, trim($_POST['unit'] ?? ''),
                         $cost, trim($_POST['supplier'] ?? ''), $_POST['purchase_date'] ?: null, (float)($_POST['used_qty'] ?? 0), $qty * $cost];
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $db->prepare("UPDATE project_materials SET name=?,category=?,quantity=?,unit=?,cost=?,supplier=?,purchase_date=?,used_qty=?,total_cost=? WHERE id=? AND project_id=?")
                       ->execute([$vals[1],$vals[2],$vals[3],$vals[4],$vals[5],$vals[6],$vals[7],$vals[8],$vals[9],$id,$pid]);
                    flash('Material updated.');
                } else {
                    $db->prepare("INSERT INTO project_materials (project_id,name,category,quantity,unit,cost,supplier,purchase_date,used_qty,total_cost) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute($vals);
                    flash('Material added.');
                }
            }
            break;

        case 'expense':
            if (($_POST['op'] ?? '') === 'delete') {
                $db->prepare("DELETE FROM project_expenses WHERE id=? AND project_id=?")->execute([(int)$_POST['id'], $pid]);
                flash('Expense deleted.', 'info');
            } else {
                $db->prepare("INSERT INTO project_expenses (project_id,exp_date,category,description,amount) VALUES (?,?,?,?,?)")
                   ->execute([$pid, $_POST['exp_date'] ?: date('Y-m-d'), trim($_POST['category'] ?? ''), trim($_POST['description'] ?? ''), (float)($_POST['amount'] ?? 0)]);
                flash('Expense added.');
            }
            break;

        case 'estimate':
            if (($_POST['op'] ?? '') === 'delete') {
                $db->prepare("DELETE FROM project_estimates WHERE id=? AND project_id=?")->execute([(int)$_POST['id'], $pid]);
                flash('Line removed.', 'info');
            } else {
                $qty = (float)($_POST['qty'] ?? 0); $rate = (float)($_POST['rate'] ?? 0);
                $db->prepare("INSERT INTO project_estimates (project_id,description,unit,qty,rate,amount) VALUES (?,?,?,?,?,?)")
                   ->execute([$pid, trim($_POST['description'] ?? ''), trim($_POST['unit'] ?? ''), $qty, $rate, $qty * $rate]);
                flash('Estimate line added.');
            }
            break;

        case 'progress':
            if (($_POST['op'] ?? '') === 'delete') {
                $db->prepare("DELETE FROM project_progress WHERE id=? AND project_id=?")->execute([(int)$_POST['id'], $pid]);
                flash('Update removed.', 'info');
            } else {
                $pct = max(0, min(100, (int)($_POST['percent'] ?? 0)));
                $db->prepare("INSERT INTO project_progress (project_id,log_date,stage,percent,status,note) VALUES (?,?,?,?,?,?)")
                   ->execute([$pid, $_POST['log_date'] ?: date('Y-m-d'), trim($_POST['stage'] ?? ''), $pct, trim($_POST['status'] ?? ''), trim($_POST['note'] ?? '')]);
                $db->prepare("UPDATE projects SET progress=? WHERE id=?")->execute([$pct, $pid]); // keep headline % in sync
                flash('Progress logged.');
            }
            break;

        case 'document':
            if (($_POST['op'] ?? '') === 'delete') {
                $row = $db->prepare("SELECT filename FROM project_documents WHERE id=? AND project_id=?");
                $row->execute([(int)$_POST['id'], $pid]);
                $fn = $row->fetchColumn();
                if ($fn && is_file(__DIR__ . '/' . $fn)) @unlink(__DIR__ . '/' . $fn);
                $db->prepare("DELETE FROM project_documents WHERE id=? AND project_id=?")->execute([(int)$_POST['id'], $pid]);
                flash('Document deleted.', 'info');
            } else {
                $stored = null;
                if (!empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                    $okExt = ['pdf','png','jpg','jpeg','gif','webp','doc','docx','xls','xlsx','txt','dwg'];
                    if (in_array($ext, $okExt, true) && $_FILES['file']['size'] <= 8 * 1024 * 1024) {
                        $dir = __DIR__ . '/uploads/proj' . $pid;
                        if (!is_dir($dir)) @mkdir($dir, 0775, true);
                        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($_FILES['file']['name'], PATHINFO_FILENAME));
                        $rel = 'uploads/proj' . $pid . '/' . uniqid() . '_' . $safe . '.' . $ext;
                        if (@move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/' . $rel)) $stored = $rel;
                        else flash('File could not be saved (folder not writable) — record kept without file.', 'warning');
                    } else {
                        flash('Unsupported file type or file too large (max 8MB) — record kept without file.', 'warning');
                    }
                }
                $db->prepare("INSERT INTO project_documents (project_id,title,category,doc_date,filename,note) VALUES (?,?,?,?,?,?)")
                   ->execute([$pid, trim($_POST['title'] ?? 'Document'), trim($_POST['category'] ?? ''), $_POST['doc_date'] ?: date('Y-m-d'), $stored, trim($_POST['note'] ?? '')]);
                flash('Document added.');
            }
            break;

        case 'note':
            if (($_POST['op'] ?? '') === 'delete') {
                $db->prepare("DELETE FROM project_notes WHERE id=? AND project_id=?")->execute([(int)$_POST['id'], $pid]);
                flash('Note deleted.', 'info');
            } else {
                $body = trim($_POST['body'] ?? '');
                if ($body !== '') { $db->prepare("INSERT INTO project_notes (project_id,body) VALUES (?,?)")->execute([$pid, $body]); flash('Note added.'); }
            }
            break;

        case 'project_delete':
            foreach (['project_materials','project_expenses','project_estimates','project_progress','project_documents','project_notes','project_details'] as $t) {
                $db->prepare("DELETE FROM `$t` WHERE project_id=?")->execute([$pid]);
            }
            $db->prepare("DELETE FROM projects WHERE id=?")->execute([$pid]);
            flash('Project deleted.', 'info');
            redirect('workspace.php');
    }
    redirect($back);
}

/* ============================ GET ============================ */
$pid     = (int) ($_GET['project'] ?? 0);
$section = $_GET['section'] ?? 'overview';
if (!isset($SECTIONS[$section])) $section = 'overview';
$newForm = isset($_GET['new']);

$allProjects = $db->query("SELECT id,name,status,progress,budget FROM projects ORDER BY id DESC")->fetchAll();

$project = null;
if ($pid > 0) {
    $st = $db->prepare("SELECT * FROM projects WHERE id=?"); $st->execute([$pid]); $project = $st->fetch() ?: null;
    if (!$project) { $pid = 0; }
}

$page = 'workspace';
$page_title = $project ? $project['name'] : 'Project Workspace';
if (!$project && !$newForm) $action = ['label' => 'New Project', 'href' => 'workspace.php?new=1'];
require __DIR__ . '/inc/header.php';

$badge = ['active'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','on-hold'=>'bg-amber-100 text-amber-700','planning'=>'bg-slate-100 text-slate-600'];

/* ---------------- NEW PROJECT FORM ---------------- */
if ($newForm) : ?>
  <div class="p-4 sm:p-6">
    <a href="workspace.php" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-4"><?= icon('arrow-left',15) ?> All projects</a>
    <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <h2 class="font-bold text-slate-800 font-display text-lg mb-4">Create a new project</h2>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?><input type="hidden" name="entity" value="project_new">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?= field_input('Project name','name','','text','e.g. Green Villa') ?>
          <?= field_input('Type','type','','text','Residential / Commercial') ?>
          <?= field_select('Status','status',['planning'=>'Planning','active'=>'Active','on-hold'=>'On Hold','completed'=>'Completed'],'planning') ?>
          <?= field_input('Total budget ('.APP_CURRENCY.')','budget','','number','0','step="0.01"') ?>
          <?= field_input('Start date','start_date',date('Y-m-d'),'date') ?>
          <?= field_input('Site engineer / manager','manager','','text','Name') ?>
        </div>
        <div class="flex gap-3 pt-1">
          <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl">Create project</button>
          <a href="workspace.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a>
        </div>
      </form>
    </div>
  </div>
  <?php require __DIR__ . '/inc/footer.php'; exit;
endif;

/* ---------------- PROJECT LIST VIEW ---------------- */
if (!$project) : ?>
  <div class="p-4 sm:p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-lg font-bold text-slate-800 font-display">All Projects</h2>
        <p class="text-xs text-slate-400">Select a project to open its dedicated dashboard</p>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <a href="workspace.php?new=1" class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 hover:border-brand hover:bg-blue-50/40 text-slate-400 hover:text-brand transition-colors p-6 min-h-[150px]">
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-brand flex items-center justify-center"><?= icon('plus',22) ?></div>
        <span class="text-sm font-semibold">New Project</span>
      </a>
      <?php foreach ($allProjects as $p): ?>
        <a href="workspace.php?project=<?= $p['id'] ?>" class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all p-5 min-h-[150px] flex flex-col">
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-brand flex items-center justify-center"><?= icon('building',18) ?></div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg <?= $badge[$p['status']]??'bg-slate-100 text-slate-600' ?>"><?= ucfirst(str_replace('-',' ',$p['status'])) ?></span>
          </div>
          <h3 class="font-bold text-slate-800 font-display leading-tight mb-1"><?= e($p['name']) ?></h3>
          <p class="text-xs text-slate-400 mb-3"><?= money_short($p['budget']) ?> budget</p>
          <div class="mt-auto flex items-center gap-2">
            <div class="flex-1 bg-slate-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-blue-500" style="width:<?= (int)$p['progress'] ?>%"></div></div>
            <span class="text-[11px] text-slate-500"><?= (int)$p['progress'] ?>%</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!$allProjects): ?><p class="text-slate-400 text-sm mt-6">No projects yet — click <strong>New Project</strong> to create your first one.</p><?php endif; ?>
  </div>
  <?php require __DIR__ . '/inc/footer.php'; exit;
endif;

/* ---------------- PROJECT DASHBOARD ---------------- */
// load details + aggregates (all scoped to this project only)
ensure_details($db, $pid);
$d = $db->prepare("SELECT * FROM project_details WHERE project_id=?"); $d->execute([$pid]); $det = $d->fetch() ?: [];
$materialCost = (float) $db->query("SELECT COALESCE(SUM(total_cost),0) FROM project_materials WHERE project_id=$pid")->fetchColumn();
$expenseTotal = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM project_expenses WHERE project_id=$pid")->fetchColumn();
$labourCost   = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM project_expenses WHERE project_id=$pid AND category='Labour'")->fetchColumn();
$budget       = (float) $project['budget'];
$totalExpenses= $materialCost + $expenseTotal;
$remaining    = $budget - $totalExpenses;
$progress     = (int) $project['progress'];

$cur = APP_CURRENCY;
$secUrl = fn($s) => 'workspace.php?project=' . $pid . '&section=' . $s;
?>
<div class="flex flex-col lg:flex-row gap-4 p-4 sm:p-6">

  <!-- Secondary section sidebar -->
  <aside class="lg:w-56 flex-shrink-0">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 lg:sticky lg:top-4">
      <a href="workspace.php" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 mb-2 px-1"><?= icon('arrow-left',14) ?> All projects</a>
      <select onchange="if(this.value)location.href=this.value" class="w-full mb-3 border border-slate-200 rounded-lg px-2 py-2 text-sm bg-slate-50 font-semibold text-slate-700">
        <?php foreach ($allProjects as $p): ?>
          <option value="<?= $secUrl2 = 'workspace.php?project='.$p['id'].'&section='.$section ?>" <?= $p['id']==$pid?'selected':'' ?>><?= e($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <nav class="flex lg:flex-col gap-1 overflow-x-auto scrollbar-hide -mx-1 px-1">
        <?php foreach ($SECTIONS as $slug => $meta): $on = $slug === $section; ?>
          <a href="<?= $secUrl($slug) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors flex-shrink-0 <?= $on?'bg-brand text-white shadow-sm':'text-slate-600 hover:bg-slate-100' ?>">
            <span class="flex-shrink-0"><?= icon($meta[1],17) ?></span><span><?= e($meta[0]) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
  </aside>

  <!-- Section content -->
  <div class="flex-1 min-w-0 space-y-4">
    <?php
    /* helper for a labelled read/edit row title */
    function section_head($title, $sub = '') { echo '<div class="mb-1"><h2 class="text-lg font-bold text-slate-800 font-display">'.e($title).'</h2>'.($sub?'<p class="text-xs text-slate-400">'.e($sub).'</p>':'').'</div>'; }

    /* ---- OVERVIEW ---- */
    if ($section === 'overview'):
        $cards = [
            ['Total Budget', money_short($budget), 'dollar', 'bg-blue-50 text-blue-600'],
            ['Total Expenses', money_short($totalExpenses), 'wallet', 'bg-rose-50 text-rose-500'],
            ['Material Cost', money_short($materialCost), 'package', 'bg-violet-50 text-violet-600'],
            ['Labour Cost', money_short($labourCost), 'users', 'bg-amber-50 text-amber-600'],
            ['Progress', $progress.'%', 'activity', 'bg-emerald-50 text-emerald-600'],
            ['Remaining Budget', money_short($remaining), 'trending-up', ($remaining<0?'bg-rose-50 text-rose-500':'bg-teal-50 text-teal-600')],
        ];
        // expense analysis data
        $expByCat = $db->query("SELECT category, SUM(amount) t FROM project_expenses WHERE project_id=$pid GROUP BY category")->fetchAll();
        $analysis = [];
        if ($materialCost > 0) $analysis[] = ['Materials', $materialCost];
        foreach ($expByCat as $r) $analysis[] = [$r['category'] ?: 'Uncategorised', (float)$r['t']];
        $amax = 1; foreach ($analysis as $a) $amax = max($amax, $a[1]);
        $colors = ['#1D4ED8','#10B981','#F59E0B','#EF4444','#8B5CF6','#0EA5E9','#EC4899','#14B8A6','#F97316'];
        $ring = 'conic-gradient(#1D4ED8 0deg '.($progress*3.6).'deg,#E2E8F0 '.($progress*3.6).'deg 360deg)';
    ?>
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div><h2 class="text-lg font-bold text-slate-800 font-display"><?= e($project['name']) ?></h2>
          <p class="text-xs text-slate-400"><?= e($project['type'] ?: 'Project') ?> · <span class="<?= 'px-2 py-0.5 rounded-lg text-[11px] font-semibold '.($badge[$project['status']]??'') ?>"><?= ucfirst(str_replace('-',' ',$project['status'])) ?></span></p></div>
        <form method="post" onsubmit="return confirm('Delete this entire project and all its data?')">
          <?= csrf_field() ?><input type="hidden" name="entity" value="project_delete"><input type="hidden" name="project_id" value="<?= $pid ?>">
          <button class="text-xs text-rose-500 hover:text-rose-600 flex items-center gap-1"><?= icon('trash',14) ?> Delete project</button>
        </form>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
        <?php foreach ($cards as $c): ?>
          <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
            <div class="w-9 h-9 rounded-xl <?= $c[3] ?> flex items-center justify-center mb-2"><?= icon($c[2],17) ?></div>
            <p class="text-xl font-bold text-slate-900 font-display"><?= e($c[1]) ?></p>
            <p class="text-xs text-slate-500"><?= e($c[0]) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col items-center justify-center">
          <h3 class="font-bold text-slate-900 text-sm mb-3 font-display self-start">Project Progress</h3>
          <div class="rounded-full flex items-center justify-center" style="width:150px;height:150px;background:<?= $ring ?>">
            <div class="bg-white rounded-full flex flex-col items-center justify-center" style="width:104px;height:104px">
              <span class="text-2xl font-bold text-slate-800 font-display"><?= $progress ?>%</span>
              <span class="text-[10px] text-slate-400">complete</span>
            </div>
          </div>
          <div class="w-full mt-4 flex items-center justify-between text-xs">
            <span class="text-slate-500">Budget used</span>
            <span class="font-semibold text-slate-700"><?= $budget>0?round($totalExpenses/$budget*100):0 ?>%</span>
          </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <h3 class="font-bold text-slate-900 text-sm mb-4 font-display">Expense Analysis</h3>
          <div class="space-y-3">
            <?php foreach ($analysis as $i => $a): ?>
              <div>
                <div class="flex justify-between text-xs mb-1"><span class="text-slate-600 font-medium"><?= e($a[0]) ?></span><span class="text-slate-500"><?= money($a[1]) ?></span></div>
                <div class="bg-slate-100 rounded-full h-2.5"><div class="h-2.5 rounded-full" style="width:<?= $a[1]/$amax*100 ?>%;background:<?= $colors[$i % count($colors)] ?>"></div></div>
              </div>
            <?php endforeach; ?>
            <?php if (!$analysis): ?><p class="text-sm text-slate-400">No expenses or materials recorded yet.</p><?php endif; ?>
          </div>
        </div>
      </div>

    <?php
    /* ---- PROFILE-TYPE SECTIONS (single form -> project_details) ---- */
    elseif (isset($DETAIL_COLS[$section])):
        $labels = [
            'customer_name'=>'Customer name','customer_company'=>'Company','customer_email'=>'Email','customer_phone'=>'Phone',
            'owner_name'=>'Owner name','owner_email'=>'Owner email','owner_phone'=>'Owner phone','owner_address'=>'Owner address',
            'site_address'=>'Site address','site_city'=>'City','site_state'=>'State','site_pincode'=>'Pincode','site_maplink'=>'Google Maps link',
            'plot_length'=>'Length (ft)','plot_width'=>'Width (ft)','plot_area'=>'Plot area (sq ft)','builtup_sqft'=>'Built-up area (sq ft)',
            'construction_type'=>'Construction type','structure_type'=>'Structure type','foundation_type'=>'Foundation type','roofing_type'=>'Roofing type',
            'num_floors'=>'Number of floors','num_units'=>'Number of units','construction_notes'=>'Construction notes',
        ];
        section_head($SECTIONS[$section][0], 'Information for this project only');
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-3xl">
          <form method="post" class="space-y-4">
            <?= csrf_field() ?><input type="hidden" name="entity" value="details"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="<?= e($section) ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <?php foreach ($DETAIL_COLS[$section] as $col):
                  $val = $det[$col] ?? '';
                  if ($col === 'construction_notes') { echo field_textarea($labels[$col], $col, $val); }
                  elseif (in_array($col, ['plot_length','plot_width','plot_area','builtup_sqft'])) { echo field_input($labels[$col], $col, $val, 'number', '', 'step="0.01"'); }
                  elseif (in_array($col, ['num_floors','num_units'])) { echo field_input($labels[$col], $col, $val, 'number'); }
                  elseif ($col === 'customer_email' || $col === 'owner_email') { echo field_input($labels[$col], $col, $val, 'email'); }
                  else { echo field_input($labels[$col], $col, $val, 'text'); }
              endforeach; ?>
            </div>
            <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl">Save</button>
          </form>
        </div>

    <?php
    /* ---- MATERIAL MANAGEMENT (full CRUD) ---- */
    elseif ($section === 'materials'):
        $mid = (int)($_GET['mid'] ?? 0);
        $editM = null;
        if ($mid) { $q = $db->prepare("SELECT * FROM project_materials WHERE id=? AND project_id=?"); $q->execute([$mid,$pid]); $editM = $q->fetch() ?: null; }
        $mats = $db->query("SELECT * FROM project_materials WHERE project_id=$pid ORDER BY id DESC")->fetchAll();
        $matCats = ['Cement','Steel','Sand','Bricks','Aggregate','Tiles','Paint','Plumbing','Electrical','Wood','Hardware','Other'];
        section_head('Material Management', 'Track construction materials for this project');
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                <th class="px-4 py-3 font-semibold">Material</th><th class="px-4 py-3 font-semibold hidden md:table-cell">Supplier</th>
                <th class="px-4 py-3 font-semibold text-right">Qty</th><th class="px-4 py-3 font-semibold text-right">Used</th>
                <th class="px-4 py-3 font-semibold text-right">Left</th><th class="px-4 py-3 font-semibold text-right">Unit Cost</th>
                <th class="px-4 py-3 font-semibold text-right">Total</th><th class="px-4 py-3"></th>
              </tr></thead>
              <tbody class="divide-y divide-slate-100">
                <?php foreach ($mats as $m): $left = (float)$m['quantity'] - (float)$m['used_qty']; ?>
                  <tr class="hover:bg-slate-50/70">
                    <td class="px-4 py-3"><p class="font-semibold text-slate-800"><?= e($m['name']) ?></p><p class="text-xs text-slate-400"><?= e($m['category']) ?> · <?= e($m['purchase_date'] ?: '—') ?></p></td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-500"><?= e($m['supplier'] ?: '—') ?></td>
                    <td class="px-4 py-3 text-right text-slate-600"><?= rtrim(rtrim(number_format($m['quantity'],2),'0'),'.') ?> <span class="text-[10px] text-slate-400"><?= e($m['unit']) ?></span></td>
                    <td class="px-4 py-3 text-right text-slate-600"><?= rtrim(rtrim(number_format($m['used_qty'],2),'0'),'.') ?></td>
                    <td class="px-4 py-3 text-right font-semibold <?= $left<=0?'text-rose-500':'text-emerald-600' ?>"><?= rtrim(rtrim(number_format($left,2),'0'),'.') ?></td>
                    <td class="px-4 py-3 text-right text-slate-600"><?= money($m['cost']) ?></td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800"><?= money($m['total_cost']) ?></td>
                    <td class="px-4 py-3"><div class="flex items-center justify-end gap-1.5">
                      <a href="<?= $secUrl('materials') ?>&mid=<?= $m['id'] ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center"><?= icon('edit',15) ?></a>
                      <form method="post" onsubmit="return confirm('Delete this material?')"><?= csrf_field() ?><input type="hidden" name="entity" value="material"><input type="hidden" name="op" value="delete"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="materials"><input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button>
                      </form>
                    </div></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (!$mats): ?><tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No materials yet. Add one below.</td></tr><?php endif; ?>
              </tbody>
              <?php if ($mats): ?><tfoot><tr class="bg-slate-50 border-t border-slate-100"><td colspan="6" class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Total material cost</td><td class="px-4 py-2.5 text-right font-bold text-slate-800"><?= money($materialCost) ?></td><td></td></tr></tfoot><?php endif; ?>
            </table>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <p class="text-sm font-bold text-slate-800 font-display mb-3"><?= $editM?'Edit material':'Add material' ?></p>
          <form method="post" class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?= csrf_field() ?><input type="hidden" name="entity" value="material"><input type="hidden" name="op" value="save"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="materials"><input type="hidden" name="id" value="<?= (int)($editM['id']??0) ?>">
            <div class="col-span-2"><?= field_input('Material name','name',$editM['name']??'','text','e.g. Portland Cement') ?></div>
            <?= field_select('Category','category',array_combine($matCats,$matCats),$editM['category']??'Cement') ?>
            <?= field_input('Supplier','supplier',$editM['supplier']??'','text','Supplier name') ?>
            <?= field_input('Quantity','quantity',$editM['quantity']??'','number','0','step="0.01"') ?>
            <?= field_input('Unit','unit',$editM['unit']??'','text','Bags / MT / Cu.ft') ?>
            <?= field_input('Unit cost ('.$cur.')','cost',$editM['cost']??'','number','0','step="0.01"') ?>
            <?= field_input('Used quantity','used_qty',$editM['used_qty']??'0','number','0','step="0.01"') ?>
            <?= field_input('Purchase date','purchase_date',$editM['purchase_date']??date('Y-m-d'),'date') ?>
            <div class="col-span-2 md:col-span-4 flex gap-2">
              <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon($editM?'edit':'plus',16) ?><?= $editM?'Save changes':'Add material' ?></button>
              <?php if ($editM): ?><a href="<?= $secUrl('materials') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl">Cancel</a><?php endif; ?>
            </div>
          </form>
          <p class="text-[11px] text-slate-400 mt-2">Total cost is calculated automatically as Quantity × Unit cost. Remaining = Quantity − Used.</p>
        </div>

    <?php
    /* ---- COST ESTIMATION ---- */
    elseif ($section === 'estimation'):
        $items = $db->query("SELECT * FROM project_estimates WHERE project_id=$pid ORDER BY id")->fetchAll();
        $estTotal = 0; foreach ($items as $it) $estTotal += (float)$it['amount'];
        section_head('Cost Estimation', 'Build a line-item estimate for this project');
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
              <th class="px-4 py-3 font-semibold">Description</th><th class="px-4 py-3 font-semibold hidden sm:table-cell">Unit</th>
              <th class="px-4 py-3 font-semibold text-right">Qty</th><th class="px-4 py-3 font-semibold text-right">Rate</th>
              <th class="px-4 py-3 font-semibold text-right">Amount</th><th class="px-4 py-3"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($items as $it): ?>
                <tr class="hover:bg-slate-50/70">
                  <td class="px-4 py-3 font-medium text-slate-800"><?= e($it['description']) ?></td>
                  <td class="px-4 py-3 hidden sm:table-cell text-slate-500"><?= e($it['unit']) ?></td>
                  <td class="px-4 py-3 text-right text-slate-600"><?= rtrim(rtrim(number_format($it['qty'],2),'0'),'.') ?></td>
                  <td class="px-4 py-3 text-right text-slate-600"><?= money($it['rate']) ?></td>
                  <td class="px-4 py-3 text-right font-semibold text-slate-800"><?= money($it['amount']) ?></td>
                  <td class="px-4 py-3 text-right"><form method="post" onsubmit="return confirm('Remove line?')"><?= csrf_field() ?><input type="hidden" name="entity" value="estimate"><input type="hidden" name="op" value="delete"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="estimation"><input type="hidden" name="id" value="<?= $it['id'] ?>"><button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button></form></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$items): ?><tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No estimate lines yet.</td></tr><?php endif; ?>
            </tbody>
            <?php if ($items): ?><tfoot><tr class="bg-slate-50 border-t border-slate-100"><td colspan="4" class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Estimated total</td><td class="px-4 py-2.5 text-right font-bold text-brand"><?= money($estTotal) ?></td><td></td></tr></tfoot><?php endif; ?>
          </table></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <p class="text-sm font-bold text-slate-800 font-display mb-3">Add estimate line</p>
          <form method="post" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <?= csrf_field() ?><input type="hidden" name="entity" value="estimate"><input type="hidden" name="op" value="add"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="estimation">
            <div class="col-span-2"><?= field_input('Description','description','','text','e.g. RCC slab casting') ?></div>
            <?= field_input('Unit','unit','','text','Sq.ft / Nos') ?>
            <?= field_input('Qty','qty','1','number','0','step="0.01"') ?>
            <?= field_input('Rate ('.$cur.')','rate','0','number','0','step="0.01"') ?>
            <div class="col-span-2 md:col-span-5"><button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('plus',16) ?>Add line</button></div>
          </form>
        </div>

    <?php
    /* ---- EXPENSE TRACKER ---- */
    elseif ($section === 'expenses'):
        $exps = $db->query("SELECT * FROM project_expenses WHERE project_id=$pid ORDER BY exp_date DESC, id DESC")->fetchAll();
        $expCats = ['Labour','Materials','Equipment','Subcontractor','Transport','Permits','Utilities','Misc'];
        section_head('Expense Tracker', 'Record day-to-day project expenses');
        ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">Tracked expenses</p><p class="text-xl font-bold text-slate-900 font-display"><?= money($expenseTotal) ?></p></div>
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">Labour cost</p><p class="text-xl font-bold text-amber-600 font-display"><?= money($labourCost) ?></p></div>
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">+ Material cost</p><p class="text-xl font-bold text-violet-600 font-display"><?= money($materialCost) ?></p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
              <th class="px-4 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Description</th><th class="px-4 py-3 font-semibold">Category</th><th class="px-4 py-3 font-semibold text-right">Amount</th><th class="px-4 py-3"></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($exps as $x): ?>
                <tr class="hover:bg-slate-50/70">
                  <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?= e($x['exp_date']) ?></td>
                  <td class="px-4 py-3 text-slate-800"><?= e($x['description']) ?></td>
                  <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600"><?= e($x['category']) ?></span></td>
                  <td class="px-4 py-3 text-right font-bold text-slate-800"><?= money($x['amount']) ?></td>
                  <td class="px-4 py-3 text-right"><form method="post" onsubmit="return confirm('Delete expense?')"><?= csrf_field() ?><input type="hidden" name="entity" value="expense"><input type="hidden" name="op" value="delete"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="expenses"><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center"><?= icon('trash',15) ?></button></form></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$exps): ?><tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No expenses yet.</td></tr><?php endif; ?>
            </tbody>
          </table></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <p class="text-sm font-bold text-slate-800 font-display mb-3">Add expense</p>
          <form method="post" class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <?= csrf_field() ?><input type="hidden" name="entity" value="expense"><input type="hidden" name="op" value="add"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="expenses">
            <div class="col-span-2"><?= field_input('Description','description','','text','e.g. Mason wages week 3') ?></div>
            <?= field_select('Category','category',array_combine($expCats,$expCats),'Labour') ?>
            <?= field_input('Amount ('.$cur.')','amount','','number','0','step="0.01"') ?>
            <?= field_input('Date','exp_date',date('Y-m-d'),'date') ?>
            <div class="col-span-2 md:col-span-4"><button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('plus',16) ?>Add expense</button></div>
          </form>
        </div>

    <?php
    /* ---- CONSTRUCTION PROGRESS ---- */
    elseif ($section === 'progress'):
        $logs = $db->query("SELECT * FROM project_progress WHERE project_id=$pid ORDER BY log_date DESC, id DESC")->fetchAll();
        section_head('Construction Progress', 'Log stage-wise progress updates');
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <div class="flex items-center gap-4">
            <div class="flex-1"><div class="flex justify-between text-xs mb-1"><span class="text-slate-500">Overall progress</span><span class="font-semibold text-slate-700"><?= $progress ?>%</span></div>
              <div class="bg-slate-100 rounded-full h-3"><div class="h-3 rounded-full bg-blue-500" style="width:<?= $progress ?>%"></div></div></div>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <p class="text-sm font-bold text-slate-800 font-display mb-3">Log an update</p>
          <form method="post" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <?= csrf_field() ?><input type="hidden" name="entity" value="progress"><input type="hidden" name="op" value="add"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="progress">
            <div class="col-span-2"><?= field_input('Stage','stage','','text','e.g. Foundation, Roofing') ?></div>
            <?= field_input('Progress %','percent',$progress,'number','0','min="0" max="100"') ?>
            <?= field_input('Status','status','','text','On track / Delayed') ?>
            <?= field_input('Date','log_date',date('Y-m-d'),'date') ?>
            <div class="col-span-2 md:col-span-5"><?= field_input('Note','note','','text','Optional details') ?></div>
            <div class="col-span-2 md:col-span-5"><button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('plus',16) ?>Log update</button></div>
          </form>
        </div>
        <div class="space-y-2">
          <?php foreach ($logs as $lg): ?>
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex items-center gap-4">
              <div class="w-11 h-11 rounded-xl bg-blue-50 text-brand flex items-center justify-center font-bold text-sm flex-shrink-0"><?= (int)$lg['percent'] ?>%</div>
              <div class="flex-1 min-w-0"><p class="font-semibold text-slate-800"><?= e($lg['stage'] ?: 'Update') ?></p><p class="text-xs text-slate-400"><?= e($lg['log_date']) ?><?= $lg['status']?' · '.e($lg['status']):'' ?><?= $lg['note']?' · '.e($lg['note']):'' ?></p></div>
              <form method="post" onsubmit="return confirm('Remove update?')"><?= csrf_field() ?><input type="hidden" name="entity" value="progress"><input type="hidden" name="op" value="delete"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="progress"><input type="hidden" name="id" value="<?= $lg['id'] ?>"><button class="text-slate-300 hover:text-rose-500"><?= icon('x',16) ?></button></form>
            </div>
          <?php endforeach; ?>
          <?php if (!$logs): ?><p class="text-sm text-slate-400">No progress updates yet.</p><?php endif; ?>
        </div>

    <?php
    /* ---- DOCUMENTS ---- */
    elseif ($section === 'documents'):
        $docs = $db->query("SELECT * FROM project_documents WHERE project_id=$pid ORDER BY id DESC")->fetchAll();
        section_head('Documents', 'Store plans, permits, agreements & photos');
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <p class="text-sm font-bold text-slate-800 font-display mb-3">Add document</p>
          <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <?= csrf_field() ?><input type="hidden" name="entity" value="document"><input type="hidden" name="op" value="save"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="documents">
            <div class="col-span-2"><?= field_input('Title','title','','text','e.g. Approved Building Plan') ?></div>
            <?= field_input('Category','category','','text','Plan / Permit / Photo') ?>
            <?= field_input('Date','doc_date',date('Y-m-d'),'date') ?>
            <div class="col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1.5">File (optional, max 8MB)</label><input type="file" name="file" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-brand file:font-semibold"></div>
            <div class="col-span-2"><?= field_input('Note','note','','text','Optional') ?></div>
            <div class="col-span-2 md:col-span-4"><button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('plus',16) ?>Add document</button></div>
          </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($docs as $doc): ?>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
              <div class="flex items-start justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center"><?= icon('file-text',18) ?></div>
                <form method="post" onsubmit="return confirm('Delete document?')"><?= csrf_field() ?><input type="hidden" name="entity" value="document"><input type="hidden" name="op" value="delete"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="documents"><input type="hidden" name="id" value="<?= $doc['id'] ?>"><button class="text-slate-300 hover:text-rose-500"><?= icon('trash',15) ?></button></form>
              </div>
              <p class="font-semibold text-slate-800 text-sm"><?= e($doc['title']) ?></p>
              <p class="text-xs text-slate-400"><?= e($doc['category'] ?: '—') ?> · <?= e($doc['doc_date'] ?: '') ?></p>
              <?php if ($doc['note']): ?><p class="text-xs text-slate-500 mt-1"><?= e($doc['note']) ?></p><?php endif; ?>
              <?php if ($doc['filename']): ?><a href="<?= e($doc['filename']) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-brand font-semibold mt-2 hover:underline"><?= icon('download',13) ?> Open file</a><?php endif; ?>
            </div>
          <?php endforeach; ?>
          <?php if (!$docs): ?><p class="text-sm text-slate-400">No documents yet.</p><?php endif; ?>
        </div>

    <?php
    /* ---- NOTES ---- */
    elseif ($section === 'notes'):
        $notes = $db->query("SELECT * FROM project_notes WHERE project_id=$pid ORDER BY id DESC")->fetchAll();
        section_head('Notes', 'Quick notes & reminders for this project');
        ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <form method="post" class="flex flex-col gap-3">
            <?= csrf_field() ?><input type="hidden" name="entity" value="note"><input type="hidden" name="op" value="add"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="notes">
            <textarea name="body" rows="3" required placeholder="Write a note..." class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
            <button class="self-start px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('plus',16) ?>Add note</button>
          </form>
        </div>
        <div class="space-y-2">
          <?php foreach ($notes as $n): ?>
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 flex items-start gap-3">
              <span class="text-amber-500 mt-0.5"><?= icon('sticky-note',16) ?></span>
              <p class="flex-1 text-sm text-slate-700 whitespace-pre-wrap"><?= e($n['body']) ?></p>
              <span class="text-[11px] text-slate-400 whitespace-nowrap"><?= e(substr($n['created_at'],0,10)) ?></span>
              <form method="post" onsubmit="return confirm('Delete note?')"><?= csrf_field() ?><input type="hidden" name="entity" value="note"><input type="hidden" name="op" value="delete"><input type="hidden" name="project_id" value="<?= $pid ?>"><input type="hidden" name="section" value="notes"><input type="hidden" name="id" value="<?= $n['id'] ?>"><button class="text-slate-300 hover:text-rose-500"><?= icon('x',15) ?></button></form>
            </div>
          <?php endforeach; ?>
          <?php if (!$notes): ?><p class="text-sm text-slate-400">No notes yet.</p><?php endif; ?>
        </div>

    <?php
    /* ---- REPORTS ---- */
    elseif ($section === 'reports'):
        $expByCat = $db->query("SELECT category, SUM(amount) t FROM project_expenses WHERE project_id=$pid GROUP BY category ORDER BY t DESC")->fetchAll();
        $matCount = (int) $db->query("SELECT COUNT(*) FROM project_materials WHERE project_id=$pid")->fetchColumn();
        section_head('Project Report', 'Summary for '.$project['name']);
        ?>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">Budget</p><p class="text-lg font-bold text-slate-900 font-display"><?= money($budget) ?></p></div>
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">Total spent</p><p class="text-lg font-bold text-rose-500 font-display"><?= money($totalExpenses) ?></p></div>
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">Remaining</p><p class="text-lg font-bold <?= $remaining<0?'text-rose-500':'text-emerald-600' ?> font-display"><?= money($remaining) ?></p></div>
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4"><p class="text-xs text-slate-500">Progress</p><p class="text-lg font-bold text-blue-600 font-display"><?= $progress ?>%</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <h3 class="font-bold text-slate-900 text-sm mb-3 font-display">Cost breakdown</h3>
          <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-100">
              <tr><td class="py-2 text-slate-600">Material cost (<?= $matCount ?> items)</td><td class="py-2 text-right font-semibold text-slate-800"><?= money($materialCost) ?></td></tr>
              <?php foreach ($expByCat as $r): ?><tr><td class="py-2 text-slate-600"><?= e($r['category'] ?: 'Uncategorised') ?> (expenses)</td><td class="py-2 text-right font-semibold text-slate-800"><?= money($r['t']) ?></td></tr><?php endforeach; ?>
              <tr class="border-t-2 border-slate-200"><td class="py-2 font-bold text-slate-800">Total</td><td class="py-2 text-right font-bold text-brand"><?= money($totalExpenses) ?></td></tr>
            </tbody>
          </table>
          <button onclick="window.print()" class="mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl flex items-center gap-1.5"><?= icon('download',15) ?> Print / Save as PDF</button>
        </div>

    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
