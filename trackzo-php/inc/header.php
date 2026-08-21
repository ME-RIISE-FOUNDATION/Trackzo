<?php
require_once __DIR__ . '/helpers.php';
require_login();

/** $page = active slug, $page_title = topbar title, $action = ['label'=>, 'href'=>] optional */
$page       = $page       ?? 'dashboard';
$page_title = $page_title ?? 'Dashboard';
// Topbar action button — only render when a proper ['label'=>,'href'=>] array is provided.
// (CRUD pages reuse a $action string like 'form'/'list' for routing, so guard the type.)
$action     = (isset($action) && is_array($action)) ? $action : null;
$user       = current_user();

$nav = [
    ['slug' => 'dashboard',       'label' => 'Dashboard',        'icon' => 'dashboard',   'file' => 'index.php'],
    ['slug' => 'projects',        'label' => 'Projects',         'icon' => 'folder',      'file' => 'projects.php'],
    ['slug' => 'clients',         'label' => 'Clients',          'icon' => 'users',       'file' => 'clients.php'],
    ['slug' => 'estimation',      'label' => 'Estimation',       'icon' => 'calculator',  'file' => 'estimation.php'],
    ['slug' => 'materials',       'label' => 'Materials',        'icon' => 'package',     'file' => 'materials.php'],
    ['slug' => 'purchase',        'label' => 'Purchase',         'icon' => 'cart',        'file' => 'purchase.php'],
    ['slug' => 'finance',         'label' => 'Finance',          'icon' => 'dollar',      'file' => 'finance.php'],
    ['slug' => 'reports',         'label' => 'Reports',          'icon' => 'bar-chart',   'file' => 'reports.php'],
    ['slug' => 'workspace',       'label' => 'Project Workspace','icon' => 'building',     'file' => 'workspace.php'],
    ['slug' => 'calendar',        'label' => 'Calendar',         'icon' => 'calendar',    'file' => 'calendar.php'],
    ['slug' => 'account-tracker', 'label' => 'Account Tracker',  'icon' => 'credit-card', 'file' => 'account_tracker.php'],
    ['slug' => 'settings',        'label' => 'Settings',         'icon' => 'settings',    'file' => 'settings.php'],
];
if (is_admin()) {
    $nav[] = ['slug' => 'admin', 'label' => 'Admin Panel', 'icon' => 'shield', 'file' => 'admin.php'];
}
$avatar = strtoupper(substr($user['name'] ?? 'U', 0, 1));
if (!empty($user['name'])) {
    $parts = preg_split('/\s+/', trim($user['name']));
    $avatar = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> · <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { extend: {
    fontFamily: { display: ['"Plus Jakarta Sans"','sans-serif'], sans: ['Inter','sans-serif'] },
    colors: {
      navy: { 900:'#0B1F3A',800:'#0F2B4C',700:'#1A3A60',600:'#1E4A78',500:'#2563A8' },
      brand: { DEFAULT:'#1D4ED8', light:'#EFF6FF', hover:'#1E40AF' },
    },
  } }
}
</script>
<style>
  body { font-family:'Inter',sans-serif; background:#F1F5F9; color:#0F172A; -webkit-font-smoothing:antialiased; }
  .font-display { font-family:'Plus Jakarta Sans',sans-serif; }
  ::-webkit-scrollbar{width:6px;height:6px}
  ::-webkit-scrollbar-thumb{background:#CBD5E1;border-radius:999px}
  .scrollbar-hide::-webkit-scrollbar{display:none}
  .sidebar-grad{background:linear-gradient(180deg,#0B1F3A 0%,#0F2B4C 100%);box-shadow:4px 0 24px rgba(0,0,0,.18)}
  #sidebar{width:240px}
  /* Desktop icon-rail collapse (only applies >=1024px) */
  @media (min-width:1024px){
    #sidebar.is-collapsed{width:72px}
    #sidebar.is-collapsed .sb-label{display:none}
  }
</style>
</head>
<body class="h-screen">
<div class="flex h-screen overflow-hidden bg-slate-100">

  <!-- Mobile drawer backdrop -->
  <div id="sb-backdrop" onclick="closeSidebar()" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar-grad flex flex-col h-full flex-shrink-0 transition-all duration-300 fixed inset-y-0 left-0 z-50 -translate-x-full lg:static lg:translate-x-0">
    <div class="flex items-center px-4 border-b border-white/10" style="height:64px">
      <a href="index.php" class="flex items-center gap-3 overflow-hidden">
        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-brand flex-shrink-0 text-white"><?= icon('hard-hat', 20) ?></div>
        <div class="flex flex-col leading-none sb-label">
          <span class="text-white font-bold text-lg tracking-tight font-display">Trackzo</span>
          <span class="text-blue-300 text-[10px] font-medium tracking-widest uppercase">Construction ERP</span>
        </div>
      </a>
      <button onclick="chevronClick()" class="ml-auto flex items-center justify-center w-7 h-7 rounded-lg text-blue-300 hover:text-white hover:bg-white/10 transition-colors flex-shrink-0"><?= icon('chevron-left', 16) ?></button>
    </div>

    <nav class="flex-1 py-4 overflow-y-auto scrollbar-hide">
      <p class="px-4 mb-2 text-[10px] font-semibold tracking-widest text-blue-400/60 uppercase sb-label">Main Menu</p>
      <ul class="space-y-0.5 px-2">
        <?php foreach (array_slice($nav, 0, 8) as $it): $on = $it['slug'] === $page; ?>
          <li>
            <a href="<?= e($it['file']) ?>" title="<?= e($it['label']) ?>"
               class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= $on ? 'bg-brand text-white shadow-lg' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
              <span class="flex-shrink-0"><?= icon($it['icon'], 18) ?></span>
              <span class="truncate sb-label"><?= e($it['label']) ?></span>
              <?php if ($on): ?><span class="ml-auto w-1.5 h-1.5 rounded-full bg-white/80 sb-label"></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="px-4 mt-4 mb-2 text-[10px] font-semibold tracking-widest text-blue-400/60 uppercase sb-label">Management</p>
      <ul class="space-y-0.5 px-2">
        <?php foreach (array_slice($nav, 8) as $it): $on = $it['slug'] === $page; ?>
          <li>
            <a href="<?= e($it['file']) ?>" title="<?= e($it['label']) ?>"
               class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= $on ? 'bg-brand text-white shadow-lg' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
              <span class="flex-shrink-0"><?= icon($it['icon'], 18) ?></span>
              <span class="truncate sb-label"><?= e($it['label']) ?></span>
              <?php if ($on): ?><span class="ml-auto w-1.5 h-1.5 rounded-full bg-white/80 sb-label"></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="border-t border-white/10 p-3">
      <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 transition-colors">
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= e($avatar) ?></div>
        <div class="flex-1 min-w-0 sb-label">
          <p class="text-white text-sm font-semibold truncate"><?= e($user['name']) ?></p>
          <p class="text-blue-300 text-xs truncate"><?= e($user['role'] ?? 'Member') ?></p>
        </div>
        <a href="logout.php" class="text-blue-300 hover:text-white transition-colors sb-label" title="Sign out"><?= icon('logout', 15) ?></a>
      </div>
    </div>
  </aside>

  <!-- Main -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <header class="h-16 bg-white border-b border-slate-200 flex items-center px-4 sm:px-6 gap-2 sm:gap-4 flex-shrink-0" style="box-shadow:0 1px 4px rgba(0,0,0,.06)">
      <button onclick="openSidebar()" class="lg:hidden w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-colors flex-shrink-0" aria-label="Open menu">
        <?= icon('menu', 18) ?>
      </button>
      <div class="flex-1 min-w-0">
        <h1 class="text-base sm:text-lg font-bold text-slate-900 truncate font-display"><?= e($page_title) ?></h1>
        <p class="text-xs text-slate-400 hidden sm:block"><?= date('l, F j, Y') ?></p>
      </div>
      <div class="hidden md:flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 w-64 text-slate-400">
        <?= icon('search', 15) ?>
        <input type="text" placeholder="Search anything..." class="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full">
      </div>
      <button class="relative w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors flex-shrink-0">
        <?= icon('bell', 17) ?>
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] text-white font-bold flex items-center justify-center">3</span>
      </button>
      <?php if ($action): ?>
        <a href="<?= e($action['href']) ?>" class="flex items-center gap-2 px-3 sm:px-4 py-2 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex-shrink-0" title="<?= e($action['label']) ?>">
          <?= icon('plus', 16) ?><span class="hidden sm:inline"><?= e($action['label']) ?></span>
        </a>
      <?php endif; ?>
      <div class="flex items-center gap-2 flex-shrink-0">
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold"><?= e($avatar) ?></div>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto">
      <?php foreach (take_flashes() as $f):
          $c = ['success'=>'bg-emerald-50 border-emerald-200 text-emerald-700','error'=>'bg-red-50 border-red-200 text-red-600','info'=>'bg-blue-50 border-blue-200 text-blue-700','warning'=>'bg-amber-50 border-amber-200 text-amber-700'][$f['type']] ?? 'bg-slate-50 border-slate-200 text-slate-700'; ?>
        <div class="mx-6 mt-4 -mb-2 rounded-xl border px-4 py-3 text-sm font-medium <?= $c ?>"><?= e($f['msg']) ?></div>
      <?php endforeach; ?>
