<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login_admin.php');
    exit();
}
include 'db_connect.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function badgeClass($value) {
    $value = strtolower(trim((string) $value));
    if (in_array($value, ['paid', 'done', 'confirmed'], true)) return 'success';
    if (in_array($value, ['rejected', 'cancelled'], true)) return 'danger';
    return 'pending';
}

$id = trim($_GET['id'] ?? '');
if ($id === '') {
    header('Location: manage_reservations.php');
    exit();
}

$statement = mysqli_prepare($conn, 'SELECT r.*, u.full_name, u.email FROM reservations r LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ? LIMIT 1');
mysqli_stmt_bind_param($statement, 's', $id);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$reservation = mysqli_fetch_assoc($result);
mysqli_stmt_close($statement);
if (!$reservation) {
    header('Location: manage_reservations.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $robeType = $_POST['robe_type'] ?? '';
    $robeSize = $_POST['robe_size'] ?? '';
    $cap = $_POST['graduation_cap'] ?? '';
    $hood = strtoupper(trim($_POST['hood_code'] ?? ''));
    $collectionDate = $_POST['collection_date'] ?? '';
    $price = trim($_POST['total_price'] ?? '');
    $validRobes = ['Diploma', 'Degree', 'Master', 'PhD'];
    $validSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $validCaps = ['Mortar Board', 'Bonnet'];
    $date = DateTime::createFromFormat('Y-m-d', $collectionDate);
    if (!in_array($robeType, $validRobes, true) || !in_array($robeSize, $validSizes, true) || !in_array($cap, $validCaps, true) || !$date || $date->format('Y-m-d') !== $collectionDate || !is_numeric($price) || (float) $price < 0) {
        $error = 'Please complete all fields with valid reservation information.';
    } else {
        $price = number_format((float) $price, 2, '.', '');
        $update = mysqli_prepare($conn, 'UPDATE reservations SET robe_type = ?, robe_size = ?, graduation_cap = ?, hood_code = ?, collection_date = ?, total_price = ? WHERE id = ?');
        mysqli_stmt_bind_param($update, 'sssssss', $robeType, $robeSize, $cap, $hood, $collectionDate, $price, $id);
        $saved = mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        if ($saved) {
            $_SESSION['reservation_message'] = 'Reservation details updated successfully.';
            header('Location: manage_reservations.php');
            exit();
        }
        $error = 'Unable to save this reservation. Please try again.';
    }
    $reservation = array_merge($reservation, ['robe_type' => $robeType, 'robe_size' => $robeSize, 'graduation_cap' => $cap, 'hood_code' => $hood, 'collection_date' => $collectionDate, 'total_price' => $price]);
}

$status = trim((string) ($reservation['status'] ?? '')) ?: 'Pending';
$payment = trim((string) ($reservation['payment_status'] ?? '')) ?: 'Pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Reservation | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; --success:#20744a; --warning:#9b6500; --danger:#a52828; }
    * { box-sizing:border-box; }body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }.site-header { background:var(--navy-dark); border-bottom:4px solid var(--gold); color:#fff; }.header-inner { width:min(1100px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; gap:18px; }.brand { display:flex; align-items:center; gap:13px; margin-right:auto; color:#fff; text-decoration:none; }.brand img { width:49px; height:49px; padding:4px; background:#fff; border-radius:8px; object-fit:contain; }.brand strong { display:block; font-size:1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }nav { display:flex; gap:3px; align-items:center; }nav a { padding:9px 11px; border-radius:6px; color:#e7eff8; font-size:.9rem; font-weight:650; text-decoration:none; }nav a:hover,nav a:focus-visible,nav a.active { color:var(--gold); background:rgba(255,255,255,.09); outline:none; }.logout { border:1px solid rgba(255,255,255,.28); }
    main { width:min(1000px,calc(100% - 40px)); margin:auto; padding:42px 0 70px; }.back-link { display:inline-block; margin-bottom:19px; color:var(--navy); font-size:.91rem; font-weight:800; text-decoration:none; }.back-link:hover { color:#8b6505; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.65rem); }.intro { margin:11px 0 27px; color:var(--muted); line-height:1.55; }.layout { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(270px,.7fr); align-items:start; gap:24px; }.card { padding:clamp(24px,4vw,36px); border-radius:13px; background:#fff; box-shadow:0 8px 22px rgba(22,48,77,.09); }.card h2 { margin:0; color:var(--navy); font-size:1.3rem; }.card > p { margin:7px 0 24px; color:var(--muted); line-height:1.5; }.form-section + .form-section { margin-top:29px; padding-top:27px; border-top:1px solid var(--line); }.form-section h3 { margin:0 0 5px; color:var(--navy); font-size:1.1rem; }.form-section > p { margin:0 0 19px; color:var(--muted); font-size:.91rem; }.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }.field.full { grid-column:1 / -1; }label { display:block; margin-bottom:7px; color:#34465d; font-size:.9rem; font-weight:750; }input,select { width:100%; min-height:46px; padding:10px 12px; border:1px solid #bdcad8; border-radius:7px; color:var(--ink); background:#fff; font:inherit; }input:focus,select:focus { border-color:#2563a4; box-shadow:0 0 0 3px rgba(37,99,164,.13); outline:none; }.field-help { margin:7px 0 0; color:var(--muted); font-size:.81rem; }.save-button { min-height:48px; width:100%; margin-top:27px; border:0; border-radius:8px; color:var(--navy-dark); background:var(--gold); font:inherit; font-weight:800; cursor:pointer; }.save-button:hover,.save-button:focus-visible { background:#ffcf42; outline:none; }.alert { margin-bottom:20px; padding:13px 15px; border-radius:8px; color:var(--danger); background:#fde9e9; font-size:.92rem; font-weight:700; }
    .summary-card { border-top:5px solid var(--gold); }.summary-card h2 { margin-bottom:17px; }.graduate { padding:14px 0 18px; border-bottom:1px solid var(--line); }.graduate strong { display:block; color:var(--navy); }.graduate span { display:block; margin-top:4px; color:var(--muted); font-size:.88rem; word-break:break-word; }.summary-list { margin:0; padding:0; list-style:none; }.summary-list li { display:flex; justify-content:space-between; gap:15px; padding:13px 0; border-bottom:1px solid var(--line); color:var(--muted); font-size:.91rem; }.summary-list strong { color:#34465d; text-align:right; }.badges { display:flex; flex-wrap:wrap; gap:7px; margin-top:20px; }.badge { padding:5px 9px; border-radius:18px; font-size:.76rem; font-weight:800; }.badge.success { color:var(--success); background:#e2f5e9; }.badge.pending { color:var(--warning); background:#fff0cf; }.badge.danger { color:var(--danger); background:#fde7e7; }.note { margin-top:22px; padding:13px; border-left:4px solid var(--gold); border-radius:0 7px 7px 0; color:#51667c; background:#f2f7fb; font-size:.85rem; line-height:1.5; }
    @media(max-width:850px) { .header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; }.layout { grid-template-columns:1fr; }.header-inner,main { width:min(100% - 28px,1100px); }main { padding-top:29px; } }@media(max-width:520px) { .form-grid { grid-template-columns:1fr; }.field.full { grid-column:auto; }.card { border-radius:11px; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><a class="brand" href="dashboard_admin.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Administration portal</span></span></a><nav aria-label="Admin navigation"><a href="dashboard_admin.php">Dashboard</a><a class="active" href="manage_reservations.php">Reservations</a><a href="manage_inventory.php">Inventory</a><a href="manage_users.php">Users</a><a href="profile_admin.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav></div></header>
  <main>
    <a class="back-link" href="manage_reservations.php">← Back to reservations</a><p class="eyebrow">Reservation editor</p><h1>Edit booking details</h1><p class="intro">Update the attire selection, collection date or total price for reservation <?= e($id) ?>.</p>
    <div class="layout"><section class="card"><h2>Booking information</h2><p>Changes will be applied directly to this graduate’s reservation.</p><?php if ($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif; ?><form method="post"><section class="form-section"><h3>Attire selection</h3><p>Confirm the robe, size and cap requested by the graduate.</p><div class="form-grid"><div class="field"><label for="robeType">Robe type</label><select id="robeType" name="robe_type" required><?php foreach (['Diploma','Degree','Master','PhD'] as $type): ?><option value="<?= e($type) ?>" <?= $reservation['robe_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></div><div class="field"><label for="robeSize">Robe size</label><select id="robeSize" name="robe_size" required><?php foreach (['XS','S','M','L','XL','XXL'] as $size): ?><option value="<?= e($size) ?>" <?= $reservation['robe_size'] === $size ? 'selected' : '' ?>><?= e($size) ?></option><?php endforeach; ?></select></div><div class="field"><label for="gradCap">Graduation cap</label><select id="gradCap" name="graduation_cap" required><?php foreach (['Mortar Board','Bonnet'] as $cap): ?><option value="<?= e($cap) ?>" <?= $reservation['graduation_cap'] === $cap ? 'selected' : '' ?>><?= e($cap) ?></option><?php endforeach; ?></select></div><div class="field"><label for="hoodCode">Hood programme code</label><input id="hoodCode" name="hood_code" type="text" maxlength="20" value="<?= e($reservation['hood_code']) ?>" placeholder="For example: CS240"></div></div></section><section class="form-section"><h3>Collection &amp; pricing</h3><p>Set the date and verified amount for this reservation.</p><div class="form-grid"><div class="field"><label for="collectionDate">Collection date</label><input id="collectionDate" name="collection_date" type="date" value="<?= e($reservation['collection_date']) ?>" required></div><div class="field"><label for="totalPrice">Total price (RM)</label><input id="totalPrice" name="total_price" type="number" min="0" step="0.01" value="<?= e($reservation['total_price']) ?>" required><p class="field-help">Use the final agreed price for this booking.</p></div></div></section><button class="save-button" type="submit">Save reservation changes</button></form></section>
      <aside class="card summary-card"><h2>Reservation summary</h2><div class="graduate"><strong><?= e($reservation['full_name'] ?: 'Graduate') ?></strong><span><?= e($reservation['email'] ?: 'No email available') ?></span></div><ul class="summary-list"><li><span>Reservation ID</span><strong><?= e($id) ?></strong></li><li><span>Payment reference</span><strong><?= e(($reservation['payment_ref'] ?? '') ?: '—') ?></strong></li><li><span>Payment amount</span><strong>RM <?= e($reservation['total_price']) ?></strong></li></ul><div class="badges"><span class="badge <?= badgeClass($status) ?>"><?= e($status) ?></span><span class="badge <?= badgeClass($payment) ?>"><?= e($payment) ?></span></div><p class="note">Status and payment processing are managed from the reservation list. This page only edits booking details.</p></aside>
    </div>
  </main>
</body>
</html>
