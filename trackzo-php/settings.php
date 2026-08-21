<?php
require_once __DIR__ . '/inc/helpers.php';
require_login();
$db = db();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $op = $_POST['op'] ?? '';
    if ($op === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? '');
        if ($name && $email) {
            try {
                $db->prepare("UPDATE users SET name=?,email=?,role=? WHERE id=?")->execute([$name,$email,$role,$user['id']]);
                $_SESSION['user']['name'] = $name; $_SESSION['user']['email'] = $email; $_SESSION['user']['role'] = $role;
                flash('Profile updated.');
            } catch (PDOException $e) { flash('That email is already in use.', 'error'); }
        } else { flash('Name and email are required.', 'error'); }
    } elseif ($op === 'password') {
        $cur = $_POST['current'] ?? ''; $new = $_POST['new'] ?? ''; $conf = $_POST['confirm'] ?? '';
        $st = $db->prepare("SELECT password_hash FROM users WHERE id=?"); $st->execute([$user['id']]);
        $hash = $st->fetchColumn();
        if (!password_verify($cur, $hash)) flash('Current password is incorrect.', 'error');
        elseif (strlen($new) < 6) flash('New password must be at least 6 characters.', 'error');
        elseif ($new !== $conf) flash('New passwords do not match.', 'error');
        else {
            $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('Password changed.');
        }
    }
    redirect('settings.php');
}

$st = $db->prepare("SELECT * FROM users WHERE id=?"); $st->execute([$user['id']]); $u = $st->fetch();

$page = 'settings'; $page_title = 'Settings';
require __DIR__ . '/inc/header.php';
?>
<div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-4 max-w-4xl">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h3 class="font-bold text-slate-900 font-display mb-4">Profile</h3>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?><input type="hidden" name="op" value="profile">
      <?= field_input('Full name','name',$u['name'],'text') ?>
      <?= field_input('Email','email',$u['email'],'email') ?>
      <?= field_input('Role / title','role',$u['role'],'text') ?>
      <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl">Save profile</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h3 class="font-bold text-slate-900 font-display mb-4">Change password</h3>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?><input type="hidden" name="op" value="password">
      <?= field_input('Current password','current','','password') ?>
      <?= field_input('New password','new','','password') ?>
      <?= field_input('Confirm new password','confirm','','password') ?>
      <button class="px-5 py-2.5 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl">Update password</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
    <h3 class="font-bold text-slate-900 font-display mb-2">About Trackzo</h3>
    <p class="text-sm text-slate-500">Construction ERP · PHP + MySQL edition. Manage projects, clients, materials, purchases, finance and estimates in one place.</p>
    <p class="text-xs text-slate-400 mt-3">Signed in as <strong class="text-slate-600"><?= e($u['email']) ?></strong></p>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
