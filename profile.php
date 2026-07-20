<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login_user.php');
    exit();
}
include 'db_connect.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$userId = (string) $_SESSION['user_id'];
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter your full name and a valid email address.';
        $messageType = 'error';
    } else {
        $statement = mysqli_prepare($conn, 'UPDATE users SET full_name = ?, email = ? WHERE id = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'sss', $name, $email, $userId);
            $updated = mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
            if ($updated) {
                $message = 'Your profile details have been updated.';
                $messageType = 'success';
            } else {
                $message = 'We could not update your profile. Please try again.';
                $messageType = 'error';
            }
        }
    }
}

$safeUserId = mysqli_real_escape_string($conn, $userId);
$userResult = mysqli_query($conn, "SELECT full_name, email FROM users WHERE id = '$safeUserId' LIMIT 1");
$user = $userResult ? mysqli_fetch_assoc($userResult) : null;
if (!$user) {
    session_destroy();
    header('Location: login_user.php');
    exit();
}
$initial = strtoupper(substr(trim($user['full_name']), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; --success:#20744a; --danger:#a52828; }
    * { box-sizing:border-box; } body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }.site-header { background:var(--navy-dark); color:#fff; border-bottom:4px solid var(--gold); }.header-inner { width:min(1200px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; gap:18px; }.brand { display:flex; align-items:center; gap:13px; margin-right:auto; color:#fff; text-decoration:none; }.brand img { width:49px; height:49px; padding:4px; background:#fff; border-radius:8px; object-fit:contain; }.brand strong { display:block; font-size:1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }nav { display:flex; gap:3px; align-items:center; }nav a { padding:9px 11px; border-radius:6px; color:#e7eff8; font-size:.94rem; font-weight:650; text-decoration:none; }nav a:hover,nav a:focus-visible,nav a.active { color:var(--gold); background:rgba(255,255,255,.09); outline:none; }.logout { border:1px solid rgba(255,255,255,.28); }
    main { width:min(960px,calc(100% - 40px)); margin:auto; padding:44px 0 72px; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.65rem); }.page-intro > p:last-child { margin:11px 0 28px; color:var(--muted); font-size:1.05rem; line-height:1.55; }.profile-layout { display:grid; grid-template-columns:280px minmax(0,1fr); gap:24px; align-items:start; }.card { padding:30px; background:#fff; border-radius:14px; box-shadow:0 8px 22px rgba(22,48,77,.09); }.identity { color:#fff; text-align:center; background:linear-gradient(145deg,var(--navy),#0b4e89); }.avatar { display:grid; place-items:center; width:88px; height:88px; margin:0 auto 17px; border:4px solid rgba(255,255,255,.5); border-radius:50%; color:var(--navy); background:var(--gold); font-size:2.25rem; font-weight:800; }.identity h2 { margin:0; font-size:1.25rem; }.identity p { margin:7px 0 0; color:#dceafa; font-size:.92rem; word-break:break-word; }.account-note { margin-top:25px; padding-top:20px; border-top:1px solid rgba(255,255,255,.24); color:#dceafa; font-size:.86rem; line-height:1.5; text-align:left; }.details-card h2 { margin:0; color:var(--navy); font-size:1.35rem; }.details-card > p { margin:7px 0 25px; color:var(--muted); line-height:1.5; }.alert { margin:0 0 21px; padding:13px 15px; border-radius:8px; font-size:.93rem; font-weight:650; }.alert.success { color:var(--success); background:#e3f5e9; }.alert.error { color:var(--danger); background:#fde9e9; }.field + .field { margin-top:20px; }label { display:block; margin-bottom:7px; color:#34465d; font-size:.92rem; font-weight:750; }input { width:100%; min-height:47px; padding:10px 12px; color:var(--ink); border:1px solid #bfcbd8; border-radius:7px; font:inherit; }input:focus { border-color:#2563a4; box-shadow:0 0 0 3px rgba(37,99,164,.13); outline:none; }.field-help { margin:7px 0 0; color:var(--muted); font-size:.82rem; }.save-button { min-height:48px; margin-top:27px; padding:0 18px; border:0; border-radius:7px; color:var(--navy-dark); background:var(--gold); font:inherit; font-weight:800; cursor:pointer; }.save-button:hover,.save-button:focus-visible { background:#ffcf42; outline:none; }.privacy { margin-top:25px; padding:15px; border-left:4px solid var(--gold); border-radius:0 7px 7px 0; color:#51667c; background:#f2f7fb; font-size:.87rem; line-height:1.5; }
    @media(max-width:820px) { .header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; }.profile-layout { grid-template-columns:1fr; }.identity { display:grid; grid-template-columns:auto 1fr; align-items:center; column-gap:18px; text-align:left; }.avatar { grid-row:span 2; margin:0; }.account-note { grid-column:1 / -1; } }@media(max-width:540px) { .header-inner,main { width:min(100% - 28px,960px); }main { padding-top:29px; }.card { padding:24px; border-radius:11px; }.identity { display:block; text-align:center; }.avatar { margin:0 auto 17px; }.account-note { text-align:left; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><a class="brand" href="dashboard_user.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Convocation Reservation System</span></span></a><nav aria-label="User navigation"><a href="dashboard_user.php">Dashboard</a><a href="book_reservation.php">Reserve Now</a><a href="view_reservation.php">My Reservation</a><a class="active" href="profile.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav></div></header>
  <main>
    <section class="page-intro"><p class="eyebrow">Account settings</p><h1>My profile</h1><p>Keep your details up to date so we can identify your reservation correctly.</p></section>
    <div class="profile-layout">
      <aside class="card identity"><div class="avatar" aria-hidden="true"><?= e($initial ?: 'U') ?></div><div><h2><?= e($user['full_name']) ?></h2><p><?= e($user['email']) ?></p></div><p class="account-note">Your profile information is used with your convocation attire reservations.</p></aside>
      <section class="card details-card"><h2>Personal details</h2><p>Update your name or email address, then save your changes.</p><?php if ($message): ?><div class="alert <?= e($messageType) ?>" role="alert"><?= e($message) ?></div><?php endif; ?><form method="post"><div class="field"><label for="fullName">Full name</label><input id="fullName" name="full_name" type="text" value="<?= e($user['full_name']) ?>" autocomplete="name" required></div><div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="<?= e($user['email']) ?>" autocomplete="email" required><p class="field-help">Use an email address you can access for reservation updates.</p></div><button class="save-button" type="submit">Save profile changes</button></form><p class="privacy">Your password is not displayed or changed on this page. Contact the administrator if you need account access help.</p></section>
    </div>
  </main>
</body>
</html>
