<?php
require_once __DIR__ . '/inc/helpers.php';

if (current_user()) redirect('index.php');

$signinError = '';
$signupError = '';
$old = ['name' => '', 'in_email' => '', 'up_email' => ''];

// Are there any accounts yet? (also tells us if the DB is installed)
$hasUsers = null;
try {
    $hasUsers = (int) db()->query("SELECT COUNT(*) FROM users")->fetchColumn() > 0;
} catch (PDOException $e) {
    $hasUsers = null; // tables missing -> needs install
}

$mode = ($hasUsers === false) ? 'signup' : 'signin';  // first-time visitors land on Sign Up
if (isset($_GET['mode']) && in_array($_GET['mode'], ['signin', 'signup'], true)) {
    $mode = $_GET['mode']; // allow ?mode=signup deep links
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasUsers !== null) {
    check_csrf();
    $mode = ($_POST['mode'] ?? 'signin') === 'signup' ? 'signup' : 'signin';

    if ($mode === 'signup') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $conf  = $_POST['confirm'] ?? '';
        $old['name'] = $name; $old['up_email'] = $email;

        if ($name === '' || $email === '' || $pass === '') {
            $signupError = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signupError = 'Please enter a valid email address.';
        } elseif (strlen($pass) < 6) {
            $signupError = 'Password must be at least 6 characters.';
        } elseif ($pass !== $conf) {
            $signupError = 'Passwords do not match.';
        } else {
            $exists = db()->prepare("SELECT 1 FROM users WHERE email=? LIMIT 1");
            $exists->execute([$email]);
            if ($exists->fetchColumn()) {
                $signupError = 'An account with that email already exists.';
            } else {
                $role = (strtolower($email) === strtolower(ADMIN_EMAIL)) ? 'Administrator' : 'Member'; // only ADMIN_EMAIL is admin
                $ins = db()->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)");
                $ins->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
                $newId = (int) db()->lastInsertId();
                db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$newId]);
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => $newId, 'name' => $name, 'email' => $email, 'role' => $role];
                redirect('index.php');
            }
        }
    } else { // signin
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $old['in_email'] = $email;
        if ($email === '' || $pass === '') {
            $signinError = 'Please enter email and password.';
        } else {
            $st = db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
            $st->execute([$email]);
            $u = $st->fetch();
            if ($u && password_verify($pass, $u['password_hash'])) {
                db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$u['id']]);
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
                redirect('index.php');
            } else {
                $signinError = 'Invalid email or password.';
            }
        }
    }
}
$needInstall = ($hasUsers === null);
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in · <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{display:['"Plus Jakarta Sans"','sans-serif'],sans:['Inter','sans-serif']},colors:{brand:{DEFAULT:'#1D4ED8',hover:'#1E40AF'}}}}}</script>
<style>
  body{font-family:'Inter',sans-serif}.font-display{font-family:'Plus Jakarta Sans',sans-serif}
  .fld{width:100%;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;font-size:14px;color:#334155;background:#f8fafc}
  .fld:focus{outline:none;border-color:#60a5fa;box-shadow:0 0 0 3px #bfdbfe}
</style>
</head>
<body class="min-h-screen flex" style="background:#F1F5F9">

  <!-- Brand panel -->
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
      <p class="text-blue-200 text-sm leading-relaxed mb-8">The all-in-one ERP platform for builders and contractors to manage every project from foundation to handover.</p>
      <div class="grid grid-cols-2 gap-3">
        <?php foreach ([['Projects','Track budgets & progress'],['Clients','Manage relationships'],['Finance','Income & expenses in ₹'],['Reports','Export to Excel & PDF']] as $s): ?>
          <div class="bg-white/10 rounded-xl p-3">
            <p class="text-white font-bold text-sm font-display"><?= $s[0] ?></p>
            <p class="text-blue-200 text-xs"><?= $s[1] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <p class="text-blue-300 text-xs">© <?= date('Y') ?> Trackzo. All rights reserved.</p>
  </div>

  <!-- Auth panel -->
  <div class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
      <div class="flex items-center gap-3 mb-8 lg:hidden">
        <div class="w-10 h-10 rounded-xl bg-brand flex items-center justify-center text-white"><?= icon('hard-hat', 22) ?></div>
        <div><p class="font-bold text-xl text-slate-900 font-display">Trackzo</p><p class="text-slate-400 text-xs">Construction ERP</p></div>
      </div>

      <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <?php if ($needInstall): ?>
          <h1 class="text-xl font-bold text-slate-900 mb-2 font-display">Almost there</h1>
          <p class="text-slate-500 text-sm mb-5">The database hasn't been set up yet. Run the installer once to create the tables, then come back to create your account.</p>
          <a href="install.php" class="w-full flex items-center justify-center gap-2 py-3 bg-brand hover:bg-brand-hover text-white font-semibold rounded-xl text-sm">Run installer</a>
        <?php else: ?>

        <h1 id="formTitle" class="text-2xl font-bold text-slate-900 mb-1 font-display">Welcome back</h1>
        <p id="formSub" class="text-slate-400 text-sm mb-6">Sign in to your Trackzo workspace</p>

        <!-- Toggle -->
        <div class="bg-slate-100 rounded-xl p-1 flex mb-6 text-sm font-semibold">
          <button type="button" id="tabIn" onclick="setMode('signin')" class="flex-1 py-2 rounded-lg transition-all">Sign In</button>
          <button type="button" id="tabUp" onclick="setMode('signup')" class="flex-1 py-2 rounded-lg transition-all">Sign Up</button>
        </div>

        <!-- Sliding track -->
        <div class="overflow-hidden">
          <div id="track" class="flex w-[200%] transition-transform duration-300 ease-out">

            <!-- Sign In -->
            <div class="w-1/2">
              <form method="post" class="space-y-4">
                <?= csrf_field() ?><input type="hidden" name="mode" value="signin">
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email address</label>
                  <input type="email" name="email" value="<?= e($old['in_email']) ?>" placeholder="you@company.com" class="fld">
                </div>
                <div>
                  <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-semibold text-slate-600">Password</label>
                    <span class="text-xs text-blue-600">Forgot password?</span>
                  </div>
                  <input type="password" name="password" placeholder="••••••••" class="fld">
                </div>
                <?php if ($signinError): ?><div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-600"><?= e($signinError) ?></div><?php endif; ?>
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-brand hover:bg-brand-hover text-white font-semibold rounded-xl text-sm"><?= icon('login', 16) ?> Sign In</button>
              </form>
            </div>

            <!-- Sign Up -->
            <div class="w-1/2">
              <form method="post" class="space-y-4">
                <?= csrf_field() ?><input type="hidden" name="mode" value="signup">
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full name</label>
                  <input type="text" name="name" value="<?= e($old['name']) ?>" placeholder="Your name" class="fld">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email address</label>
                  <input type="email" name="email" value="<?= e($old['up_email']) ?>" placeholder="you@company.com" class="fld">
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
                    <input type="password" name="password" placeholder="Min 6 chars" class="fld">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Confirm</label>
                    <input type="password" name="confirm" placeholder="Repeat" class="fld">
                  </div>
                </div>
                <?php if ($signupError): ?><div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-600"><?= e($signupError) ?></div><?php endif; ?>
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-brand hover:bg-brand-hover text-white font-semibold rounded-xl text-sm"><?= icon('plus', 16) ?> Create account</button>
              </form>
            </div>

          </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
          <span id="switchHint"></span>
          <button type="button" id="switchBtn" onclick="setMode(document.getElementById('track').dataset.mode==='signup'?'signin':'signup')" class="text-blue-600 font-semibold hover:underline"></button>
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php if (!$needInstall): ?>
<script>
var A='bg-white text-brand shadow-sm', I='text-slate-500';
function setMode(m){
  var track=document.getElementById('track'); track.dataset.mode=m;
  track.style.transform = m==='signup' ? 'translateX(-50%)' : 'translateX(0)';
  var tin=document.getElementById('tabIn'), tup=document.getElementById('tabUp');
  tin.className='flex-1 py-2 rounded-lg transition-all '+(m==='signin'?A:I);
  tup.className='flex-1 py-2 rounded-lg transition-all '+(m==='signup'?A:I);
  document.getElementById('formTitle').textContent = m==='signup'?'Create your account':'Welcome back';
  document.getElementById('formSub').textContent   = m==='signup'?'Set up your Trackzo workspace':'Sign in to your Trackzo workspace';
  document.getElementById('switchHint').textContent = m==='signup'?'Already have an account? ':"Don't have an account? ";
  document.getElementById('switchBtn').textContent  = m==='signup'?'Sign in':'Create one';
}
setMode(<?= json_encode($mode) ?>);
</script>
<?php endif; ?>
</body></html>
