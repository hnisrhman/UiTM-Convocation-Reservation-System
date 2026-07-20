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
function reservationUrl($filter, $search) {
    $query = [];
    if ($filter !== 'all') $query['filter'] = $filter;
    if ($search !== '') $query['search'] = $search;
    return 'manage_reservations.php' . ($query ? '?' . http_build_query($query) : '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $reservationId = trim($_POST['res_id'] ?? '');
    $newStatus = $_POST['status'] ?? '';
    $allowedStatuses = ['Pending', 'Done', 'Rejected'];
    if ($reservationId !== '' && in_array($newStatus, $allowedStatuses, true)) {
        $statement = mysqli_prepare($conn, 'UPDATE reservations SET status = ? WHERE id = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'ss', $newStatus, $reservationId);
            mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
            $_SESSION['reservation_message'] = 'Reservation status updated successfully.';
        }
    }
    $returnFilter = $_POST['filter'] ?? 'all';
    $returnSearch = trim($_POST['search'] ?? '');
    header('Location: ' . reservationUrl($returnFilter, $returnSearch));
    exit();
}

$filter = $_GET['filter'] ?? 'all';
$allowedFilters = ['all', 'Pending', 'Done', 'Rejected'];
if (!in_array($filter, $allowedFilters, true)) $filter = 'all';
$search = trim($_GET['search'] ?? '');
$conditions = [];
if ($filter !== 'all') $conditions[] = "r.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
if ($search !== '') {
    $searchTerm = mysqli_real_escape_string($conn, $search);
    $conditions[] = "(r.id LIKE '%$searchTerm%' OR r.user_id LIKE '%$searchTerm%' OR r.robe_type LIKE '%$searchTerm%' OR r.collection_date LIKE '%$searchTerm%' OR u.full_name LIKE '%$searchTerm%' OR u.email LIKE '%$searchTerm%')";
}
$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$result = mysqli_query($conn, "SELECT r.*, u.full_name, u.email FROM reservations r LEFT JOIN users u ON r.user_id = u.id $where ORDER BY r.collection_date DESC, r.id DESC");
$reservations = [];
if ($result) while ($row = mysqli_fetch_assoc($result)) $reservations[] = $row;
$totalCount = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM reservations'))['total'] ?? 0;
$pendingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations WHERE status IS NULL OR status = '' OR status = 'Pending'"))['total'] ?? 0;
$doneCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations WHERE status = 'Done'"))['total'] ?? 0;
$message = $_SESSION['reservation_message'] ?? '';
unset($_SESSION['reservation_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Reservations | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; --success:#20744a; --warning:#9b6500; --danger:#a52828; }
    * { box-sizing:border-box; }body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }.site-header { background:var(--navy-dark); border-bottom:4px solid var(--gold); color:#fff; }.header-inner { width:min(1240px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; gap:18px; }.brand { display:flex; align-items:center; gap:13px; margin-right:auto; color:#fff; text-decoration:none; }.brand img { width:49px; height:49px; padding:4px; background:#fff; border-radius:8px; object-fit:contain; }.brand strong { display:block; font-size:1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }nav { display:flex; gap:3px; align-items:center; }nav a { padding:9px 11px; border-radius:6px; color:#e7eff8; font-size:.9rem; font-weight:650; text-decoration:none; }nav a:hover,nav a:focus-visible,nav a.active { color:var(--gold); background:rgba(255,255,255,.09); outline:none; }.logout { border:1px solid rgba(255,255,255,.28); }
    main { width:min(1240px,calc(100% - 40px)); margin:auto; padding:42px 0 70px; }.heading { display:flex; align-items:end; justify-content:space-between; gap:20px; margin-bottom:26px; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.7rem); }.heading p:last-child { margin:11px 0 0; color:var(--muted); line-height:1.55; }.dashboard-link { color:var(--navy); font-size:.93rem; font-weight:800; text-decoration:none; }.dashboard-link:hover { color:#8b6505; }
    .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:25px; }.stat { padding:19px 21px; border-radius:11px; background:#fff; box-shadow:0 6px 17px rgba(22,48,77,.07); }.stat span { display:block; color:var(--muted); font-size:.86rem; font-weight:700; }.stat strong { display:block; margin-top:5px; color:var(--navy); font-size:1.6rem; }.filter-bar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:17px; }.filter-bar h2 { margin:0; color:var(--navy); font-size:1.3rem; }.filter-tabs { display:flex; flex-wrap:wrap; gap:7px; }.filter-tabs a { padding:8px 11px; border:1px solid #cbd7e3; border-radius:6px; color:var(--navy); background:#fff; font-size:.86rem; font-weight:750; text-decoration:none; }.filter-tabs a:hover,.filter-tabs a.active { border-color:var(--navy); color:#fff; background:var(--navy); }.search-bar { display:flex; gap:8px; margin-bottom:17px; }.search-bar input { flex:1; min-height:43px; padding:9px 12px; border:1px solid #bdcad8; border-radius:7px; color:var(--ink); background:#fff; font:inherit; }.search-bar input:focus { border-color:#2563a4; box-shadow:0 0 0 3px rgba(37,99,164,.13); outline:none; }.search-bar button { min-height:43px; padding:0 15px; border:0; border-radius:7px; color:#fff; background:var(--navy); font:inherit; font-weight:750; cursor:pointer; }.search-bar button:hover { background:#0b4e89; }.clear-search { display:grid; place-items:center; min-width:43px; border:1px solid #bdcad8; border-radius:7px; color:var(--navy); background:#fff; font-weight:800; text-decoration:none; }.clear-search:hover { background:#eef4fa; }.alert { margin-bottom:17px; padding:13px 15px; border-radius:8px; color:var(--success); background:#e3f5e9; font-size:.93rem; font-weight:700; }
    .table-card { overflow:hidden; background:#fff; border-radius:12px; box-shadow:0 7px 20px rgba(22,48,77,.08); }.table-wrap { overflow-x:auto; }.reservation-table { width:100%; min-width:1050px; border-collapse:collapse; }.reservation-table th { padding:15px 17px; color:#50647a; background:#f6f9fc; border-bottom:1px solid var(--line); font-size:.74rem; letter-spacing:.05em; text-align:left; text-transform:uppercase; }.reservation-table td { padding:17px; border-bottom:1px solid #eaf0f5; color:#42536a; font-size:.9rem; vertical-align:top; }.reservation-table tr:last-child td { border-bottom:0; }.reservation-table strong { color:var(--navy); }.subtle { display:block; margin-top:3px; color:var(--muted); font-size:.79rem; }.badge { display:inline-block; padding:5px 8px; border-radius:16px; font-size:.73rem; font-weight:800; }.badge.success { color:var(--success); background:#e2f5e9; }.badge.pending { color:var(--warning); background:#fff0cf; }.badge.danger { color:var(--danger); background:#fde7e7; }.status-form { display:flex; gap:7px; align-items:center; }.status-form select { min-height:34px; padding:5px 25px 5px 8px; border:1px solid #bdcad8; border-radius:5px; color:var(--ink); background:#fff; font:inherit; font-size:.84rem; }.status-form button { min-height:34px; padding:0 9px; border:0; border-radius:5px; color:#fff; background:var(--navy); font:inherit; font-size:.8rem; font-weight:750; cursor:pointer; }.status-form button:hover { background:#0b4e89; }.edit-link { display:inline-block; margin-top:10px; color:var(--navy); font-size:.84rem; font-weight:750; text-decoration:none; }.edit-link:hover { color:#8b6505; }.empty { padding:52px 25px; text-align:center; color:var(--muted); }.empty strong { display:block; margin-bottom:7px; color:var(--navy); font-size:1.2rem; }
    @media(max-width:820px) { .header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; }.header-inner,main { width:min(100% - 28px,1240px); }main { padding-top:29px; }.heading { align-items:start; flex-direction:column; }.filter-bar { align-items:start; flex-direction:column; }.stats { grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><a class="brand" href="dashboard_admin.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Administration portal</span></span></a><nav aria-label="Admin navigation"><a href="dashboard_admin.php">Dashboard</a><a class="active" href="manage_reservations.php">Reservations</a><a href="manage_inventory.php">Inventory</a><a href="manage_users.php">Users</a><a href="profile_admin.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav></div></header>
  <main>
    <section class="heading"><div><p class="eyebrow">Booking operations</p><h1>Manage reservations</h1><p>Review graduate bookings, check payments and update collection progress.</p></div><a class="dashboard-link" href="dashboard_admin.php">← Back to dashboard</a></section>
    <section class="stats" aria-label="Reservation counts"><div class="stat"><span>All reservations</span><strong><?= e($totalCount) ?></strong></div><div class="stat"><span>Pending action</span><strong><?= e($pendingCount) ?></strong></div><div class="stat"><span>Completed collections</span><strong><?= e($doneCount) ?></strong></div></section>
    <form class="search-bar" method="get" role="search"><input type="hidden" name="filter" value="<?= e($filter) ?>"><input type="search" name="search" value="<?= e($search) ?>" placeholder="Search by graduate, email, reservation ID, attire or collection date" aria-label="Search reservations"><button type="submit">Search</button><?php if ($search !== ''): ?><a class="clear-search" href="<?= e(reservationUrl($filter, '')) ?>" aria-label="Clear search">×</a><?php endif; ?></form>
    <section class="filter-bar"><h2>Reservation list<?= $search !== '' ? ' · Search results' : '' ?></h2><nav class="filter-tabs" aria-label="Reservation filters"><a class="<?= $filter === 'all' ? 'active' : '' ?>" href="<?= e(reservationUrl('all', $search)) ?>">All</a><a class="<?= $filter === 'Pending' ? 'active' : '' ?>" href="<?= e(reservationUrl('Pending', $search)) ?>">Pending</a><a class="<?= $filter === 'Done' ? 'active' : '' ?>" href="<?= e(reservationUrl('Done', $search)) ?>">Done</a><a class="<?= $filter === 'Rejected' ? 'active' : '' ?>" href="<?= e(reservationUrl('Rejected', $search)) ?>">Rejected</a></nav></section>
    <?php if ($message): ?><div class="alert" role="status"><?= e($message) ?></div><?php endif; ?>
    <section class="table-card"><div class="table-wrap"><table class="reservation-table"><thead><tr><th>Graduate</th><th>Reservation</th><th>Collection</th><th>Payment</th><th>Total</th><th>Status &amp; action</th></tr></thead><tbody>
      <?php if ($reservations): foreach ($reservations as $reservation):
        $id = (string) ($reservation['id'] ?? '');
        $status = trim((string) ($reservation['status'] ?? '')) ?: 'Pending';
        $payment = trim((string) ($reservation['payment_status'] ?? '')) ?: 'Pending';
      ?>
        <tr><td><strong><?= e($reservation['full_name'] ?: 'Graduate') ?></strong><span class="subtle"><?= e($reservation['email'] ?: 'No email available') ?></span><span class="subtle">User ID: <?= e($reservation['user_id']) ?></span></td><td><strong><?= e($reservation['robe_type']) ?></strong><span class="subtle">Size <?= e($reservation['robe_size']) ?> · <?= e($reservation['graduation_cap']) ?></span><span class="subtle">Hood: <?= e(($reservation['hood_code'] ?? '') ?: '—') ?></span><span class="subtle">Ref: <?= e($id) ?></span></td><td><?= e($reservation['collection_date']) ?></td><td><span class="badge <?= badgeClass($payment) ?>"><?= e($payment) ?></span><span class="subtle"><?= e(($reservation['payment_ref'] ?? '') ?: 'No reference') ?></span></td><td><strong>RM <?= e($reservation['total_price']) ?></strong></td><td><span class="badge <?= badgeClass($status) ?>"><?= e($status) ?></span><form class="status-form" method="post"><input type="hidden" name="res_id" value="<?= e($id) ?>"><input type="hidden" name="filter" value="<?= e($filter) ?>"><input type="hidden" name="search" value="<?= e($search) ?>"><select name="status" aria-label="Update status for <?= e($id) ?>"><option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option><option value="Done" <?= $status === 'Done' ? 'selected' : '' ?>>Done</option><option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option></select><button type="submit" name="update_status">Update</button></form><a class="edit-link" href="edit_reservation_admin.php?id=<?= urlencode($id) ?>">Edit booking →</a></td></tr>
      <?php endforeach; else: ?><tr><td class="empty" colspan="6"><strong>No reservations found</strong>There are no reservations in this view.</td></tr><?php endif; ?>
    </tbody></table></div></section>
  </main>
</body>
</html>
