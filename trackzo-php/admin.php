<?php
require_once __DIR__ . '/inc/helpers.php';
require_admin();               // only ADMIN_EMAIL passes this
$db = db();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (($_POST['op'] ?? '') === 'delete_user') {
        $uid = (int) ($_POST['id'] ?? 0);
        $t = $db->prepare("SELECT email FROM users WHERE id=?"); $t->execute([$uid]);
        $email = $t->fetchColumn();
        if ($email !== false && strtolower($email) === strtolower(ADMIN_EMAIL)) {
            flash('The admin account cannot be deleted.', 'error');
        } elseif ($email !== false) {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
            flash('User account deleted.', 'info');
        }
    }
    redirect('admin.php');
}

// ---- Stats ----
$totalUsers   = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$customers    = (int) $db->query("SELECT COUNT(*) FROM users WHERE LOWER(email)<>LOWER(" . $db->quote(ADMIN_EMAIL) . ")")->fetchColumn();
$activeToday  = (int) $db->query("SELECT COUNT(*) FROM users WHERE last_login >= CURDATE()")->fetchColumn();
$totalProjects= (int) $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$activeProj   = (int) $db->query("SELECT COUNT(*) FROM projects WHERE status='active'")->fetchColumn();
$totalClients = (int) $db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$portBudget   = (float) $db->query("SELECT COALESCE(SUM(budget),0) FROM projects")->fetchColumn();
$income       = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income'")->fetchColumn();
$expense      = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense'")->fetchColumn();

$users    = $db->query("SELECT * FROM users ORDER BY (LOWER(email)=LOWER(" . $db->quote(ADMIN_EMAIL) . ")) DESC, created_at DESC, id DESC")->fetchAll();
$projects = $db->query("SELECT p.*, c.name client_name FROM projects p LEFT JOIN clients c ON c.id=p.client_id ORDER BY p.id DESC")->fetchAll();

$stats = [
    ['Total Users', $totalUsers, 'users', 'bg-blue-50 text-blue-600', $customers.' customer'.($customers==1?'':'s').' + 1 admin'],
    ['Active Today', $activeToday, 'activity', 'bg-emerald-50 text-emerald-600', 'logged in today'],
    ['Total Projects', $totalProjects, 'building', 'bg-violet-50 text-violet-600', $activeProj.' active'],
    ['Total Clients', $totalClients, 'user', 'bg-amber-50 text-amber-600', 'in directory'],
    ['Portfolio Budget', money_short($portBudget), 'dollar', 'bg-teal-50 text-teal-600', 'all projects'],
    ['Net (Inc − Exp)', money_short($income - $expense), 'wallet', ($income-$expense<0?'bg-rose-50 text-rose-500':'bg-blue-50 text-blue-600'), 'company-wide'],
];
$badge = ['active'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','on-hold'=>'bg-amber-100 text-amber-700','planning'=>'bg-slate-100 text-slate-600'];

$page = 'admin'; $page_title = 'Admin Panel';
require __DIR__ . '/inc/header.php';
?>
<div class="p-4 sm:p-6 space-y-4">

  <div class="rounded-2xl p-5 text-white flex items-center gap-4" style="background:linear-gradient(135deg,#0F2B4C 0%,#1D4ED8 100%)">
    <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0"><?= icon('shield',24) ?></div>
    <div>
      <p class="text-lg font-bold font-display">Administrator Console</p>
      <p class="text-blue-200 text-sm">Admin access is restricted to <strong><?= e(ADMIN_EMAIL) ?></strong>. You can see every account and all company work here.</p>
    </div>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
    <?php foreach ($stats as $s): ?>
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
        <div class="w-9 h-9 rounded-xl <?= $s[3] ?> flex items-center justify-center mb-2"><?= icon($s[2],17) ?></div>
        <p class="text-2xl font-bold text-slate-900 font-display"><?= e($s[1]) ?></p>
        <p class="text-xs text-slate-500"><?= e($s[0]) ?></p>
        <p class="text-[11px] text-slate-400 mt-0.5"><?= e($s[4]) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Users / logins -->
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <div><h3 class="font-bold text-slate-900 text-sm font-display">User Accounts & Logins</h3>
        <p class="text-xs text-slate-400">Everyone who has signed up to the system</p></div>
      <span class="text-xs bg-blue-50 text-blue-600 font-semibold px-2.5 py-1 rounded-lg"><?= $totalUsers ?> total</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 font-semibold">User</th>
          <th class="px-5 py-3 font-semibold hidden md:table-cell">Joined</th>
          <th class="px-5 py-3 font-semibold">Last login</th>
          <th class="px-5 py-3 font-semibold text-center">Access</th>
          <th class="px-5 py-3 font-semibold text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($users as $u):
            $parts = preg_split('/\s+/', trim($u['name'])); $ini = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
            $isAdmin = strtolower($u['email']) === strtolower(ADMIN_EMAIL); ?>
          <tr class="hover:bg-slate-50/70 <?= $isAdmin?'bg-blue-50/40':'' ?>">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full <?= $isAdmin?'bg-gradient-to-br from-blue-500 to-navy-800':'bg-gradient-to-br from-blue-400 to-blue-600' ?> flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= e($ini) ?></div>
                <div class="min-w-0"><p class="font-semibold text-slate-800 truncate"><?= e($u['name']) ?></p><p class="text-xs text-slate-400 truncate"><?= e($u['email']) ?></p></div>
              </div>
            </td>
            <td class="px-5 py-3 hidden md:table-cell text-slate-500"><?= e(substr($u['created_at'],0,10)) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= $u['last_login'] ? e(substr($u['last_login'],0,16)) : '<span class="text-slate-300">Never</span>' ?></td>
            <td class="px-5 py-3 text-center">
              <?php if ($isAdmin): ?>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-blue-100 text-blue-700 inline-flex items-center gap-1"><?= icon('shield',11) ?> Admin</span>
              <?php else: ?>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">Customer</span>
              <?php endif; ?>
            </td>
            <td class="px-5 py-3">
              <div class="flex items-center justify-end gap-1.5">
                <?php if ($isAdmin): ?>
                  <span class="text-[11px] text-slate-400">Protected</span>
                <?php else: ?>
                  <form method="post" onsubmit="return confirm('Delete this user account?')">
                    <?= csrf_field() ?><input type="hidden" name="op" value="delete_user"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center" title="Delete user"><?= icon('trash',15) ?></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- All works / projects -->
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <div><h3 class="font-bold text-slate-900 text-sm font-display">All Projects (Company Work)</h3>
        <p class="text-xs text-slate-400">Every project across the organisation</p></div>
      <span class="text-xs bg-violet-50 text-violet-600 font-semibold px-2.5 py-1 rounded-lg"><?= $totalProjects ?> projects</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
          <th class="px-5 py-3 font-semibold">Project</th>
          <th class="px-5 py-3 font-semibold hidden lg:table-cell">Client</th>
          <th class="px-5 py-3 font-semibold text-right">Budget</th>
          <th class="px-5 py-3 font-semibold text-right">Spent</th>
          <th class="px-5 py-3 font-semibold text-center">Progress</th>
          <th class="px-5 py-3 font-semibold text-center">Status</th>
          <th class="px-5 py-3 font-semibold text-right">Open</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($projects as $p): ?>
          <tr class="hover:bg-slate-50/70">
            <td class="px-5 py-3 font-semibold text-slate-800"><?= e($p['name']) ?></td>
            <td class="px-5 py-3 hidden lg:table-cell text-slate-500"><?= e($p['client_name'] ?: '—') ?></td>
            <td class="px-5 py-3 text-right text-slate-700"><?= money($p['budget']) ?></td>
            <td class="px-5 py-3 text-right text-rose-500"><?= money($p['spent']) ?></td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-2 justify-center"><div class="w-20 bg-slate-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-blue-500" style="width:<?= (int)$p['progress'] ?>%"></div></div><span class="text-[11px] text-slate-500"><?= (int)$p['progress'] ?>%</span></div>
            </td>
            <td class="px-5 py-3 text-center"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg <?= $badge[$p['status']]??'bg-slate-100 text-slate-600' ?>"><?= ucfirst(str_replace('-',' ',$p['status'])) ?></span></td>
            <td class="px-5 py-3 text-right"><a href="workspace.php?project=<?= $p['id'] ?>" class="text-xs text-blue-600 font-semibold hover:underline inline-flex items-center gap-0.5">Open <?= icon('arrow-up-right',12) ?></a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$projects): ?><tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">No projects yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
