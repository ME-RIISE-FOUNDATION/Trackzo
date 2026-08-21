<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'add') {
        $allowed = ['meeting','deadline','inspection','delivery','task'];
        $type = in_array($_POST['type'] ?? '', $allowed, true) ? $_POST['type'] : 'task';
        $db->prepare("INSERT INTO calendar_events (title,event_date,type,event_time,project_id) VALUES (?,?,?,?,?)")
           ->execute([trim($_POST['title'] ?? ''), $_POST['event_date'] ?: date('Y-m-d'), $type, $_POST['event_time'] ?: null, ((int)($_POST['project_id'] ?? 0)) ?: null]);
        flash('Event added.');
    } elseif ($op === 'delete') {
        $db->prepare("DELETE FROM calendar_events WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Event deleted.', 'info');
    }
    redirect('calendar.php' . (isset($_POST['ym']) ? '?ym=' . urlencode($_POST['ym']) : ''));
}

$ym = $_GET['ym'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
$first = strtotime($ym . '-01');
$year = (int) date('Y', $first); $month = (int) date('n', $first);
$daysIn = (int) date('t', $first);
$startDow = (int) date('w', $first); // 0=Sun
$prevYm = date('Y-m', strtotime("$ym-01 -1 month"));
$nextYm = date('Y-m', strtotime("$ym-01 +1 month"));

$st = $db->prepare("SELECT * FROM calendar_events WHERE event_date BETWEEN ? AND ? ORDER BY event_date, event_time");
$st->execute([date('Y-m-01', $first), date('Y-m-t', $first)]);
$events = [];
foreach ($st->fetchAll() as $ev) $events[(int)date('j', strtotime($ev['event_date']))][] = $ev;

$typeColor = ['meeting'=>'bg-violet-500','deadline'=>'bg-rose-500','inspection'=>'bg-blue-500','delivery'=>'bg-amber-500','task'=>'bg-emerald-500'];

$projects = $db->query("SELECT id,name FROM projects ORDER BY name")->fetchAll();
$projOpts = ['' => '— none —']; foreach ($projects as $p) $projOpts[$p['id']] = $p['name'];

$upcoming = $db->query("SELECT e.*, p.name pname FROM calendar_events e LEFT JOIN projects p ON p.id=e.project_id WHERE e.event_date >= CURDATE() ORDER BY e.event_date, e.event_time LIMIT 8")->fetchAll();

$page = 'calendar'; $page_title = 'Calendar';
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 grid grid-cols-1 xl:grid-cols-3 gap-4">
  <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-900 font-display"><?= date('F Y', $first) ?></h3>
      <div class="flex items-center gap-1">
        <a href="?ym=<?= $prevYm ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 flex items-center justify-center"><?= icon('chevron-left',16) ?></a>
        <a href="?ym=<?= date('Y-m') ?>" class="px-3 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold flex items-center">Today</a>
        <a href="?ym=<?= $nextYm ?>" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 flex items-center justify-center"><?= icon('chevron-right',16) ?></a>
      </div>
    </div>
    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-slate-400 uppercase mb-1">
      <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?><div class="py-1"><?= $d ?></div><?php endforeach; ?>
    </div>
    <div class="grid grid-cols-7 gap-1">
      <?php for ($i=0;$i<$startDow;$i++): ?><div class="min-h-20 rounded-lg bg-slate-50/40"></div><?php endfor; ?>
      <?php for ($d=1;$d<=$daysIn;$d++): $isToday = date('Y-m-d')===sprintf('%04d-%02d-%02d',$year,$month,$d); ?>
        <div class="min-h-20 rounded-lg border border-slate-100 p-1.5 <?= $isToday?'bg-blue-50 border-blue-200':'' ?>">
          <div class="text-[11px] font-semibold <?= $isToday?'text-blue-600':'text-slate-500' ?> mb-1"><?= $d ?></div>
          <?php foreach ($events[$d] ?? [] as $ev): ?>
            <div class="flex items-center gap-1 mb-0.5">
              <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 <?= $typeColor[$ev['type']]??'bg-slate-400' ?>"></span>
              <span class="text-[10px] text-slate-600 truncate" title="<?= e($ev['title']) ?>"><?= e($ev['title']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <div class="space-y-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <p class="text-sm font-bold text-slate-800 font-display mb-3">Add event</p>
      <form method="post" class="space-y-3">
        <?= csrf_field() ?><input type="hidden" name="op" value="add"><input type="hidden" name="ym" value="<?= e($ym) ?>">
        <?= field_input('Title','title','','text','Site inspection') ?>
        <div class="grid grid-cols-2 gap-3">
          <?= field_input('Date','event_date',date('Y-m-d'),'date') ?>
          <?= field_input('Time','event_time','','time') ?>
        </div>
        <?= field_select('Type','type',['task'=>'Task','meeting'=>'Meeting','deadline'=>'Deadline','inspection'=>'Inspection','delivery'=>'Delivery'],'task') ?>
        <?= field_select('Project','project_id',$projOpts,'') ?>
        <button class="w-full px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-1.5"><?= icon('plus',16) ?>Add event</button>
      </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <p class="text-sm font-bold text-slate-800 font-display mb-3">Upcoming</p>
      <div class="space-y-2.5">
        <?php foreach ($upcoming as $ev): ?>
          <div class="flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full flex-shrink-0 <?= $typeColor[$ev['type']]??'bg-slate-400' ?>"></span>
            <div class="flex-1 min-w-0"><p class="text-sm text-slate-700 truncate"><?= e($ev['title']) ?></p><p class="text-[11px] text-slate-400"><?= e($ev['event_date']) ?><?= $ev['event_time']?' · '.e($ev['event_time']):'' ?></p></div>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="op" value="delete"><input type="hidden" name="id" value="<?= $ev['id'] ?>"><input type="hidden" name="ym" value="<?= e($ym) ?>">
              <button class="text-slate-300 hover:text-rose-500" title="Delete"><?= icon('x',14) ?></button>
            </form>
          </div>
        <?php endforeach; ?>
        <?php if (!$upcoming): ?><p class="text-sm text-slate-400">No upcoming events.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
