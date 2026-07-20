<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login_admin.php');
    exit();
}
include 'db_connect.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function scalar($connection, $query, $field, $fallback = 0) {
    $result = mysqli_query($connection, $query);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return $row[$field] ?? $fallback;
}
function badgeClass($value) {
    $value = strtolower(trim((string) $value));
    if (in_array($value, ['paid', 'done', 'confirmed'], true)) return 'success';
    if (in_array($value, ['rejected', 'cancelled'], true)) return 'danger';
    return 'pending';
}

$adminId = mysqli_real_escape_string($conn, (string) $_SESSION['user_id']);
$adminResult = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '$adminId' LIMIT 1");
$admin = $adminResult ? mysqli_fetch_assoc($adminResult) : null;
$adminName = trim(explode(' ', $admin['full_name'] ?? 'Administrator')[0]);
$totalReservations = (int) scalar($conn, 'SELECT COUNT(*) AS total FROM reservations', 'total');
$paidReservations = (int) scalar($conn, "SELECT COUNT(*) AS total FROM reservations WHERE payment_status = 'Paid'", 'total');
$pendingReservations = (int) scalar($conn, "SELECT COUNT(*) AS total FROM reservations WHERE status IS NULL OR status = '' OR status = 'Pending'", 'total');
$revenue = (float) scalar($conn, "SELECT COALESCE(SUM(CAST(total_price AS DECIMAL(10,2))), 0) AS total FROM reservations WHERE payment_status = 'Paid'", 'total');
$lowStock = (int) scalar($conn, 'SELECT COUNT(*) AS total FROM products WHERE CAST(quantity AS UNSIGNED) <= 5', 'total');
$recentResult = mysqli_query($conn, 'SELECT r.*, u.full_name FROM reservations r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.collection_date DESC, r.id DESC LIMIT 6');
$recentReservations = [];
if ($recentResult) while ($row = mysqli_fetch_assoc($recentResult)) $recentReservations[] = $row;
$monthlyResult = mysqli_query($conn, "SELECT DATE_FORMAT(collection_date, '%b') AS month, SUM(CAST(total_price AS DECIMAL(10,2))) AS total FROM reservations WHERE payment_status = 'Paid' GROUP BY DATE_FORMAT(collection_date, '%Y-%m'), DATE_FORMAT(collection_date, '%b') ORDER BY MIN(collection_date) DESC LIMIT 6");
$monthlyRevenue = [];
if ($monthlyResult) while ($row = mysqli_fetch_assoc($monthlyResult)) $monthlyRevenue[] = $row;
$monthlyRevenue = array_reverse($monthlyRevenue);
$maxMonthlyRevenue = max(array_map(fn($row) => (float) $row['total'], $monthlyRevenue) ?: [1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; --success:#20744a; --warning:#9b6500; --danger:#a52828; }
    * { box-sizing:border-box; } body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }.site-header { background:var(--navy-dark); border-bottom:4px solid var(--gold); color:#fff; }.header-inner { width:min(1240px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; gap:18px; }.brand { display:flex; align-items:center; gap:13px; margin-right:auto; color:#fff; text-decoration:none; }.brand img { width:49px; height:49px; padding:4px; background:#fff; border-radius:8px; object-fit:contain; }.brand strong { display:block; font-size:1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }nav { display:flex; gap:3px; align-items:center; }nav a { padding:9px 11px; border-radius:6px; color:#e7eff8; font-size:.9rem; font-weight:650; text-decoration:none; }nav a:hover,nav a:focus-visible,nav a.active { color:var(--gold); background:rgba(255,255,255,.09); outline:none; }.logout { border:1px solid rgba(255,255,255,.28); }
    main { width:min(1240px,calc(100% - 40px)); margin:auto; padding:42px 0 70px; }.hero { display:flex; justify-content:space-between; align-items:end; gap:24px; margin-bottom:29px; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.7rem); }.hero p:last-child { margin:11px 0 0; color:var(--muted); line-height:1.55; }.primary-button { display:inline-block; padding:12px 16px; border-radius:7px; color:var(--navy-dark); background:var(--gold); font-weight:800; text-decoration:none; white-space:nowrap; }.primary-button:hover { background:#ffcf42; }
    .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }.stat { padding:21px; background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(22,48,77,.07); }.stat-label { display:flex; justify-content:space-between; color:var(--muted); font-size:.86rem; font-weight:750; }.stat-icon { color:#926200; }.stat strong { display:block; margin-top:8px; color:var(--navy); font-size:1.7rem; }.stat small { display:block; margin-top:5px; color:var(--muted); }.section-heading { display:flex; justify-content:space-between; align-items:end; gap:16px; margin:41px 0 17px; }.section-heading h2 { margin:0; color:var(--navy); font-size:1.36rem; }.section-heading p { margin:5px 0 0; color:var(--muted); }.text-link { color:var(--navy); font-size:.92rem; font-weight:800; text-decoration:none; }.text-link:hover { color:#8b6505; }
    .quick-actions { display:grid; grid-template-columns:repeat(3,1fr); gap:17px; }.quick-action { padding:22px; border:1px solid #dce5ee; border-radius:11px; background:#fff; color:var(--ink); text-decoration:none; transition:transform .15s,box-shadow .15s; }.quick-action:hover { transform:translateY(-3px); box-shadow:0 11px 22px rgba(22,48,77,.12); }.quick-action .icon { display:grid; place-items:center; width:37px; height:37px; border-radius:8px; color:var(--navy); background:#fff0bf; font-size:1.15rem; font-weight:800; }.quick-action h3 { margin:14px 0 6px; color:var(--navy); font-size:1.07rem; }.quick-action p { margin:0; color:var(--muted); font-size:.9rem; line-height:1.45; }
    .dashboard-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(300px,.75fr); gap:24px; }.card { padding:27px; background:#fff; border-radius:12px; box-shadow:0 7px 20px rgba(22,48,77,.08); }.card h2 { margin:0; color:var(--navy); font-size:1.27rem; }.card > p { margin:6px 0 19px; color:var(--muted); }.recent-table { width:100%; border-collapse:collapse; }.recent-table th { padding:0 8px 11px; color:var(--muted); border-bottom:1px solid var(--line); font-size:.74rem; letter-spacing:.05em; text-align:left; text-transform:uppercase; }.recent-table td { padding:14px 8px; border-bottom:1px solid #edf1f5; color:#3d4e63; font-size:.91rem; }.recent-table tr:last-child td { border-bottom:0; }.recent-table strong { color:var(--navy); }.badge { display:inline-block; padding:5px 8px; border-radius:16px; font-size:.73rem; font-weight:800; }.badge.success { color:var(--success); background:#e2f5e9; }.badge.pending { color:var(--warning); background:#fff0cf; }.badge.danger { color:var(--danger); background:#fde7e7; }
    .chart { display:flex; align-items:end; gap:15px; height:220px; margin-top:22px; padding:8px 0 23px; border-bottom:1px solid var(--line); }.bar-item { display:flex; flex:1; height:100%; flex-direction:column; align-items:center; justify-content:end; gap:7px; min-width:28px; }.bar { width:min(100%,40px); min-height:4px; border-radius:5px 5px 0 0; background:linear-gradient(#f8ce46,var(--gold)); }.bar-item strong { color:var(--navy); font-size:.72rem; }.bar-item span { color:var(--muted); font-size:.78rem; }.chart-empty { display:grid; place-items:center; height:220px; color:var(--muted); text-align:center; }.inventory-alert { display:flex; gap:12px; margin-top:20px; padding:15px; border-radius:8px; background:#fff6dd; color:#765400; line-height:1.45; }.inventory-alert strong { display:block; }
    @media(max-width:1000px) { .stat-grid { grid-template-columns:repeat(2,1fr); }.dashboard-grid { grid-template-columns:1fr; }.quick-actions { grid-template-columns:1fr; } }.table-wrap { overflow-x:auto; }@media(max-width:760px) { .header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; }.header-inner,main { width:min(100% - 28px,1240px); }main { padding-top:29px; }.hero { align-items:start; flex-direction:column; }.primary-button { width:100%; text-align:center; }.section-heading { align-items:start; flex-direction:column; margin-top:33px; }.card { padding:22px; }.recent-table { min-width:560px; }.stat-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><a class="brand" href="dashboard_admin.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Administration portal</span></span></a><nav aria-label="Admin navigation"><a class="active" href="dashboard_admin.php">Dashboard</a><a href="manage_reservations.php">Reservations</a><a href="manage_inventory.php">Inventory</a><a href="manage_users.php">Users</a><a href="profile_admin.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav></div></header>
  <main>
    <section class="hero"><div><p class="eyebrow">Operations overview</p><h1>Good day, <?= e($adminName) ?>.</h1><p>Monitor reservation activity, paid collections and inventory from one place.</p></div><a class="primary-button" href="manage_reservations.php">Manage reservations</a></section>
    <section class="stat-grid" aria-label="Dashboard totals"><article class="stat"><span class="stat-label">Total reservations <span class="stat-icon">◫</span></span><strong><?= e($totalReservations) ?></strong><small>All booking records</small></article><article class="stat"><span class="stat-label">Payments completed <span class="stat-icon">✓</span></span><strong><?= e($paidReservations) ?></strong><small>Confirmed payments</small></article><article class="stat"><span class="stat-label">Pending collection <span class="stat-icon">◷</span></span><strong><?= e($pendingReservations) ?></strong><small>Reservations to process</small></article><article class="stat"><span class="stat-label">Paid revenue <span class="stat-icon">RM</span></span><strong>RM <?= number_format($revenue, 2) ?></strong><small>From completed payments</small></article></section>
    <div class="section-heading"><div><h2>Quick actions</h2><p>Jump directly to daily management tasks.</p></div></div>
    <section class="quick-actions"><a class="quick-action" href="manage_reservations.php"><span class="icon">✓</span><h3>Process reservations</h3><p>Update collection status and review payment details.</p></a><a class="quick-action" href="manage_inventory.php"><span class="icon">□</span><h3>Manage inventory</h3><p>Update attire items, prices and available quantities.</p></a><a class="quick-action" href="manage_users.php"><span class="icon">◉</span><h3>Manage users</h3><p>View and manage registered system accounts.</p></a></section>
    <div class="section-heading"><div><h2>Recent reservations</h2><p>Latest bookings across the system.</p></div><a class="text-link" href="manage_reservations.php">View all reservations →</a></div>
    <section class="dashboard-grid"><article class="card"><div class="table-wrap"><table class="recent-table"><thead><tr><th>Graduate</th><th>Attire</th><th>Collection</th><th>Total</th><th>Status</th></tr></thead><tbody><?php if ($recentReservations): foreach ($recentReservations as $reservation): $status = trim((string) ($reservation['status'] ?? '')) ?: 'Pending'; ?><tr><td><strong><?= e($reservation['full_name'] ?: 'Graduate') ?></strong><br><small><?= e($reservation['id']) ?></small></td><td><?= e($reservation['robe_type']) ?><br><small><?= e($reservation['robe_size']) ?> · <?= e($reservation['graduation_cap']) ?></small></td><td><?= e($reservation['collection_date']) ?></td><td>RM <?= e($reservation['total_price']) ?></td><td><span class="badge <?= badgeClass($status) ?>"><?= e($status) ?></span></td></tr><?php endforeach; else: ?><tr><td colspan="5">No reservations have been created yet.</td></tr><?php endif; ?></tbody></table></div></article>
      <aside class="card"><h2>Revenue trend</h2><p>Paid revenue by collection month.</p><?php if ($monthlyRevenue): ?><div class="chart"><?php foreach ($monthlyRevenue as $month): $height = max(8, ((float) $month['total'] / $maxMonthlyRevenue) * 100); ?><div class="bar-item"><strong>RM <?= number_format((float) $month['total'], 0) ?></strong><div class="bar" style="height:<?= e(round($height, 2)) ?>%"></div><span><?= e($month['month']) ?></span></div><?php endforeach; ?></div><?php else: ?><div class="chart-empty">Paid revenue will appear here once reservations are completed.</div><?php endif; ?><div class="inventory-alert"><span>!</span><div><strong><?= e($lowStock) ?> low-stock item<?= $lowStock === 1 ? '' : 's' ?></strong>Review inventory quantities to keep attire available for graduates.</div></div><a class="text-link" style="display:inline-block;margin-top:16px" href="manage_inventory.php">Open inventory →</a></aside>
    </section>
  </main>
</body>
</html>
