<?php
require_once __DIR__ . '/inc/helpers.php';

if (current_user()) redirect('index.php');

$error = '';
$email = 'james.carter@trackzo.io';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($email === '' || $pass === '') {
        $error = 'Please enter email and password.';
    } else {
        try {
            $st = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $st->execute([$email]);
            $u = $st->fetch();
            if ($u && password_verify($pass, $u['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
                redirect('index.php');
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database not set up yet. Please run install.php first.';
        }
    }
}
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in · <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{display:['"Plus Jakarta Sans"','sans-serif'],sans:['Inter','sans-serif']},colors:{brand:{DEFAULT:'#1D4ED8',hover:'#1E40AF'}}}}}</script>
<style>body{font-family:'Inter',sans-serif}.font-display{font-family:'Plus Jakarta Sans',sans-serif}</style>
</head>
<body class="min-h-screen flex" style="background:#F1F5F9">

  <div class="hidden lg:flex flex-col justify-between p-10 w-[440px] flex-shrink-0" style="background:linear-gradient(160deg,#0B1F3A 0%,#1D4ED8 100%)">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white"><?= icon('hard-hat', 22) ?></div>
      <div>
        <p class="text-white font-bold text-xl tracking-tight font-display">Trackzo</p>
        <p class="text-blue-200 text-[11px] tracking-widest uppercase">Construction ERP</p>
      </div>
    </div>
    <div>
      <h2 class="text-white text-3xl font-bold mb-4 leading-tight font-display">Build Smarter.<br>Track Better.</h2>
      <p class="text-blue-200 text-sm leading-relaxed mb-8">The all-in-one ERP platform trusted by builders, contractors, and construction companies to manage every project from foundation to handover.</p>
      <div class="grid grid-cols-2 gap-3">
        <?php foreach ([['Projects Managed','1,200+'],['Active Users','8,500+'],['Countries','24'],['Uptime SLA','99.9%']] as $s): ?>
          <div class="bg-white/10 rounded-xl p-3">
            <p class="text-white font-bold text-lg font-display"><?= $s[1] ?></p>
            <p class="text-blue-200 text-xs"><?= $s[0] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <p class="text-blue-300 text-xs">© <?= date('Y') ?> Trackzo Inc. All rights reserved.</p>
  </div>

  <div class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
      <div class="flex items-center gap-3 mb-8 lg:hidden">
        <div class="w-10 h-10 rounded-xl bg-brand flex items-center justify-center text-white"><?= icon('hard-hat', 22) ?></div>
        <div><p class="font-bold text-xl text-slate-900 font-display">Trackzo</p><p class="text-slate-400 text-xs">Construction ERP</p></div>
      </div>

      <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <h1 class="text-2xl font-bold text-slate-900 mb-1 font-display">Welcome back</h1>
        <p class="text-slate-400 text-sm mb-6">Sign in to your Trackzo workspace</p>

        <form method="post" class="space-y-4">
          <?= csrf_field() ?>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email address</label>
            <input type="email" name="email" value="<?= e($email) ?>" placeholder="you@company.com"
              class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
          </div>
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-xs font-semibold text-slate-600">Password</label>
              <span class="text-xs text-blue-600">Forgot password?</span>
            </div>
            <div class="relative">
              <input type="password" name="password" id="pw" placeholder="••••••••"
                class="w-full border border-slate-200 rounded-xl px-4 py-3 pr-10 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
              <button type="button" onclick="var p=document.getElementById('pw');p.type=p.type==='password'?'text':'password'"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"><?= icon('eye', 16) ?></button>
            </div>
          </div>

          <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-600"><?= e($error) ?></div>
          <?php endif; ?>

          <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-brand hover:bg-brand-hover text-white font-semibold rounded-xl text-sm">
            <?= icon('login', 16) ?> Sign In
          </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100">
          <p class="text-xs text-slate-400 text-center">Demo login: <strong class="text-slate-600">james.carter@trackzo.io</strong> / <strong class="text-slate-600">password123</strong></p>
        </div>
      </div>
    </div>
  </div>
</body></html>
