<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login_user.php');
    exit();
}
include 'db_connect.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function displayDate($value) {
    $date = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $date ? $date->format('d M Y') : (string) $value;
}
function badgeClass($value) {
    $value = strtolower(trim((string) $value));
    if (in_array($value, ['paid', 'confirmed', 'completed'], true)) return 'success';
    if (in_array($value, ['cancelled', 'rejected'], true)) return 'danger';
    return 'pending';
}

$userId = mysqli_real_escape_string($conn, (string) $_SESSION['user_id']);
$result = mysqli_query($conn, "SELECT * FROM reservations WHERE user_id = '$userId' ORDER BY collection_date DESC, id DESC");
$reservations = [];
if ($result) while ($row = mysqli_fetch_assoc($result)) $reservations[] = $row;
$paidCount = count(array_filter($reservations, fn($row) => strtolower((string) ($row['payment_status'] ?? '')) === 'paid'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reservations | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; --success:#20744a; --warning:#9b6500; --danger:#a52828; }
    * { box-sizing:border-box; } body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }
    .site-header { background:var(--navy-dark); color:#fff; border-bottom:4px solid var(--gold); }.header-inner { width:min(1200px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; gap:18px; }.brand { display:flex; align-items:center; gap:13px; margin-right:auto; color:#fff; text-decoration:none; }.brand img { width:49px; height:49px; padding:4px; background:#fff; border-radius:8px; object-fit:contain; }.brand strong { display:block; font-size:1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }nav { display:flex; gap:3px; align-items:center; }nav a { padding:9px 11px; border-radius:6px; color:#e7eff8; font-size:.94rem; font-weight:650; text-decoration:none; }nav a:hover,nav a:focus-visible,nav a.active { color:var(--gold); background:rgba(255,255,255,.09); outline:none; }.logout { border:1px solid rgba(255,255,255,.28); }
    main { width:min(1200px,calc(100% - 40px)); margin:auto; padding:42px 0 70px; }.heading { display:flex; justify-content:space-between; align-items:end; gap:24px; margin-bottom:27px; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.65rem); }.heading p:last-child { margin:11px 0 0; color:var(--muted); line-height:1.55; }.button { display:inline-block; padding:12px 16px; border-radius:7px; color:var(--navy-dark); background:var(--gold); font-weight:800; text-decoration:none; white-space:nowrap; }.button:hover { background:#ffcf42; }
    .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:26px; }.stat { padding:20px 22px; background:#fff; border-radius:11px; box-shadow:0 6px 17px rgba(22,48,77,.07); }.stat span { display:block; color:var(--muted); font-size:.88rem; font-weight:700; }.stat strong { display:block; margin-top:4px; color:var(--navy); font-size:1.65rem; }.stat.gold strong { color:#946400; }
    .reservation-list { display:grid; gap:17px; }.reservation-card { padding:25px 27px; background:#fff; border-radius:12px; box-shadow:0 7px 20px rgba(22,48,77,.08); }.card-top { display:flex; justify-content:space-between; align-items:start; gap:18px; }.card-top h2 { margin:0; color:var(--navy); font-size:1.24rem; }.reference { margin:5px 0 0; color:var(--muted); font-size:.84rem; }.badges { display:flex; flex-wrap:wrap; justify-content:end; gap:7px; }.badge { padding:5px 9px; border-radius:20px; font-size:.76rem; font-weight:800; }.badge.success { color:var(--success); background:#e2f5e9; }.badge.pending { color:var(--warning); background:#fff0cf; }.badge.danger { color:var(--danger); background:#fde7e7; }
    .details { display:grid; grid-template-columns:repeat(5,1fr); gap:13px; margin:23px 0; padding:18px 0; border-top:1px solid var(--line); border-bottom:1px solid var(--line); }.details span { display:block; color:var(--muted); font-size:.77rem; font-weight:750; letter-spacing:.04em; text-transform:uppercase; }.details strong { display:block; margin-top:5px; color:#33465d; font-size:.94rem; }.card-bottom { display:flex; justify-content:space-between; align-items:center; gap:16px; }.price { color:var(--navy); font-size:1.18rem; font-weight:800; }.price small { color:var(--muted); font-size:.8rem; font-weight:650; }.actions { display:flex; flex-wrap:wrap; justify-content:end; gap:9px; }.action { padding:9px 12px; border:1px solid #c9d5e2; border-radius:6px; color:var(--navy); font-size:.87rem; font-weight:750; text-decoration:none; }.action:hover { background:#eef4fa; }.action.primary { border-color:var(--navy); color:#fff; background:var(--navy); }.action.primary:hover { background:#0c4a82; }.action.cancel { color:var(--danger); }.action.cancel:hover { background:#fff1f1; }
    .empty { padding:55px 28px; border:2px dashed #c9d6e3; border-radius:13px; background:#fff; text-align:center; }.empty h2 { margin:0; color:var(--navy); }.empty p { max-width:470px; margin:11px auto 22px; color:var(--muted); line-height:1.55; }
    @media(max-width:900px) { .header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; }.details { grid-template-columns:repeat(3,1fr); } }@media(max-width:600px) { .header-inner,main { width:min(100% - 28px,1200px); }main { padding-top:28px; }.heading,.card-top,.card-bottom { align-items:start; flex-direction:column; }.heading .button { width:100%; text-align:center; }.stats { grid-template-columns:1fr; }.reservation-card { padding:22px; }.badges { justify-content:start; }.details { grid-template-columns:1fr 1fr; }.actions { justify-content:start; }.price { margin-bottom:3px; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><a class="brand" href="dashboard_user.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Convocation Reservation System</span></span></a><nav aria-label="User navigation"><a href="dashboard_user.php">Dashboard</a><a href="book_reservation.php">Reserve Now</a><a class="active" href="view_reservation.php">My Reservation</a><a href="profile.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav></div></header>
  <main>
    <section class="heading"><div><p class="eyebrow">Your booking history</p><h1>My reservations</h1><p>Review your attire bookings, payment details and collection dates in one place.</p></div><a class="button" href="book_reservation.php">Reserve new attire</a></section>
    <section class="stats" aria-label="Reservation summary"><div class="stat"><span>Total reservations</span><strong><?= count($reservations) ?></strong></div><div class="stat"><span>Payments completed</span><strong><?= $paidCount ?></strong></div><div class="stat gold"><span>Collection to prepare</span><strong><?= count($reservations) - $paidCount ?></strong></div></section>
    <?php if ($reservations): ?><section class="reservation-list" aria-label="Reservations">
      <?php foreach ($reservations as $reservation):
        $id = (string) ($reservation['id'] ?? '');
        $status = trim((string) ($reservation['status'] ?? '')) ?: 'Pending';
        $payment = trim((string) ($reservation['payment_status'] ?? '')) ?: 'Pending';
      ?>
        <article class="reservation-card"><div class="card-top"><div><h2><?= e($reservation['robe_type']) ?> attire</h2><p class="reference">Reservation reference: <?= e($id) ?></p></div><div class="badges"><span class="badge <?= badgeClass($status) ?>"><?= e($status) ?></span><span class="badge <?= badgeClass($payment) ?>"><?= e($payment) ?></span></div></div>
          <div class="details"><div><span>Collection date</span><strong><?= e(displayDate($reservation['collection_date'] ?? '')) ?></strong></div><div><span>Robe size</span><strong><?= e($reservation['robe_size']) ?></strong></div><div><span>Graduation cap</span><strong><?= e($reservation['graduation_cap']) ?></strong></div><div><span>Hood code</span><strong><?= e(($reservation['hood_code'] ?? '') ?: '—') ?></strong></div><div><span>Payment reference</span><strong><?= e(($reservation['payment_ref'] ?? '') ?: '—') ?></strong></div></div>
          <div class="card-bottom"><div class="price"><small>Total paid</small><br>RM <?= e($reservation['total_price']) ?></div><div class="actions"><?php if (strtolower($payment) === 'paid'): ?><a class="action primary" href="print_receipt.php?id=<?= urlencode($id) ?>">View receipt</a><?php else: ?><span class="action">Payment pending</span><?php endif; ?><a class="action" href="edit_reservation.php?id=<?= urlencode($id) ?>">Edit</a><a class="action cancel" href="cancel_reservation.php?id=<?= urlencode($id) ?>" onclick="return confirm('Cancel this reservation?');">Cancel</a></div></div>
        </article>
      <?php endforeach; ?>
    </section><?php else: ?><section class="empty"><h2>No reservations yet</h2><p>You have not reserved any convocation attire. Start your booking now and prepare for collection day.</p><a class="button" href="book_reservation.php">Reserve attire</a></section><?php endif; ?>
  </main>
</body>
</html>
