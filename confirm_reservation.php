<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login_user.php');
    exit();
}
include 'db_connect.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

$robePrices = ['Diploma' => 15, 'Degree' => 25, 'Master' => 35, 'PhD' => 40];
$capPrices = ['Mortar Board' => 10, 'Bonnet' => 15];
$packagePrices = ['Diploma' => 20, 'Degree' => 40, 'PhD' => 60];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['robe_type'])) {
    header('Location: book_reservation.php');
    exit();
}

$robe = trim($_POST['robe_type'] ?? '');
$size = trim($_POST['robe_size'] ?? '');
$cap = trim($_POST['graduation_cap'] ?? '');
$hood = strtoupper(trim($_POST['hood_code'] ?? ''));
$collectionDate = trim($_POST['collection_date'] ?? '');
$packageSelected = ($_POST['package_selected'] ?? '0') === '1';
$validSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
$dateObject = DateTime::createFromFormat('Y-m-d', $collectionDate);

if (!isset($robePrices[$robe]) || !isset($capPrices[$cap]) || !in_array($size, $validSizes, true) || !$dateObject || $dateObject->format('Y-m-d') !== $collectionDate) {
    header('Location: book_reservation.php');
    exit();
}

$total = $robePrices[$robe] + $capPrices[$cap] + ($hood !== '' ? 10 : 0);
if ($packageSelected) {
    if ($robe === 'Master') {
        $total = $cap === 'Bonnet' ? 55 : 50;
    } elseif (isset($packagePrices[$robe])) {
        $total = $packagePrices[$robe];
    }
}
$total = number_format($total, 2, '.', '');

if (isset($_POST['payment_method'])) {
    $paymentMethod = $_POST['payment_method'];
    $allowedMethods = ['FPX', 'Card', 'E-Wallet'];
    if (!in_array($paymentMethod, $allowedMethods, true)) {
        header('Location: book_reservation.php');
        exit();
    }

    $reservationId = 'RSV' . strtoupper(uniqid());
    $paymentRef = 'PAY' . strtoupper(uniqid());
    $userId = (string) $_SESSION['user_id'];
    $status = 'Pending';
    $paymentStatus = 'Paid';
    $statement = mysqli_prepare($conn, 'INSERT INTO reservations (id, user_id, robe_size, robe_type, graduation_cap, hood_code, collection_date, total_price, status, payment_status, payment_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$statement) {
        exit('Unable to complete your reservation. Please try again.');
    }
    mysqli_stmt_bind_param($statement, 'sssssssssss', $reservationId, $userId, $size, $robe, $cap, $hood, $collectionDate, $total, $status, $paymentStatus, $paymentRef);
    $saved = mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
    if (!$saved) {
        exit('Unable to complete your reservation. Please try again.');
    }
    header('Location: print_receipt.php?id=' . urlencode($reservationId));
    exit();
}

$displayDate = $dateObject->format('d F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirm Reservation | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; }
    * { box-sizing:border-box; } body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }
    .site-header { background:var(--navy-dark); border-bottom:4px solid var(--gold); }.header-inner { width:min(1080px,calc(100% - 40px)); min-height:78px; margin:auto; display:flex; align-items:center; gap:13px; color:#fff; }.header-inner img { width:45px; height:45px; padding:4px; object-fit:contain; background:#fff; border-radius:8px; }.header-inner strong { font-size:1.05rem; }.header-inner span { display:block; margin-top:2px; color:#cbd9e7; font-size:.78rem; }
    main { width:min(960px,calc(100% - 40px)); margin:auto; padding:46px 0 70px; }.progress { display:flex; align-items:center; gap:9px; margin-bottom:22px; color:var(--muted); font-size:.9rem; font-weight:700; }.progress span { display:grid; place-items:center; width:25px; height:25px; border-radius:50%; color:var(--navy); background:var(--gold); }.progress i { width:30px; height:1px; background:#a9b8c8; }.progress .inactive { color:#fff; background:#a9b8c8; }
    .heading { margin-bottom:27px; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.6rem); }.heading > p:last-child { margin:12px 0 0; color:var(--muted); font-size:1.05rem; line-height:1.6; }
    .layout { display:grid; grid-template-columns:minmax(0,1fr) minmax(280px,.68fr); align-items:start; gap:24px; }.card { padding:clamp(25px,4vw,38px); background:#fff; border-radius:14px; box-shadow:0 8px 22px rgba(22,48,77,.09); }.card h2 { margin:0 0 19px; color:var(--navy); font-size:1.3rem; }.detail-list { margin:0; padding:0; list-style:none; }.detail-list li { display:flex; justify-content:space-between; gap:20px; padding:15px 0; border-top:1px solid var(--line); color:var(--muted); }.detail-list strong { color:var(--ink); text-align:right; }.total { display:flex; justify-content:space-between; align-items:end; margin-top:8px; padding-top:20px; border-top:2px solid var(--navy); }.total span { color:var(--muted); font-size:.9rem; font-weight:750; }.total strong { color:var(--navy); font-size:1.75rem; }
    .payment-card { border-top:5px solid var(--gold); }.payment-card > p { margin:0 0 19px; color:var(--muted); line-height:1.5; }.payment-options { display:grid; gap:10px; }.payment-option { display:flex; align-items:center; gap:11px; padding:13px; border:1px solid var(--line); border-radius:8px; cursor:pointer; }.payment-option:has(input:checked) { border-color:var(--gold); background:#fff8de; }.payment-option input { width:18px; height:18px; margin:0; accent-color:var(--navy); }.payment-option strong { color:var(--navy); }.payment-option span { display:block; margin-top:3px; color:var(--muted); font-size:.82rem; }.pay-button { width:100%; min-height:49px; margin-top:22px; border:0; border-radius:8px; color:var(--navy-dark); background:var(--gold); font:inherit; font-weight:800; cursor:pointer; }.pay-button:hover,.pay-button:focus-visible { background:#ffcf42; outline:none; }.back-link { display:block; margin-top:15px; color:var(--navy); text-align:center; font-size:.9rem; font-weight:700; text-decoration:none; }.back-link:hover { color:#8b6505; }.notice { margin-top:17px; padding:12px; border-radius:7px; color:#42607b; background:#eef5fb; font-size:.83rem; line-height:1.45; }
    @media(max-width:720px) { .layout { grid-template-columns:1fr; }.header-inner,main { width:min(100% - 28px,960px); }main { padding-top:30px; }.card { border-radius:11px; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><img src="images/logo_uitm.png" alt="UiTM"><div><strong>UiTM RobeReserve</strong><span>Convocation Reservation System</span></div></div></header>
  <main>
    <div class="progress" aria-label="Reservation progress"><span>1</span> Attire details <i></i><span>2</span> Review &amp; payment <i></i><span class="inactive">3</span> Receipt</div>
    <section class="heading"><p class="eyebrow">Almost there</p><h1>Review your reservation</h1><p>Please check your attire details, then choose a payment method to complete your reservation.</p></section>
    <div class="layout">
      <section class="card"><h2>Reservation details</h2><ul class="detail-list"><li><span>Robe</span><strong><?= e($robe) ?> · <?= e($size) ?></strong></li><li><span>Graduation cap</span><strong><?= e($cap) ?></strong></li><li><span>Hood programme code</span><strong><?= $hood !== '' ? e($hood) : 'Not selected' ?></strong></li><li><span>Collection date</span><strong><?= e($displayDate) ?></strong></li><li><span>Pricing</span><strong><?= $packageSelected ? 'Complete set package' : 'Standard pricing' ?></strong></li></ul><div class="total"><span>Total payable</span><strong>RM <?= e($total) ?></strong></div></section>
      <aside class="card payment-card"><h2>Choose payment method</h2><p>Your reservation will be saved once payment is confirmed.</p>
        <form method="post">
          <input type="hidden" name="robe_type" value="<?= e($robe) ?>"><input type="hidden" name="robe_size" value="<?= e($size) ?>"><input type="hidden" name="graduation_cap" value="<?= e($cap) ?>"><input type="hidden" name="hood_code" value="<?= e($hood) ?>"><input type="hidden" name="collection_date" value="<?= e($collectionDate) ?>"><input type="hidden" name="package_selected" value="<?= $packageSelected ? '1' : '0' ?>">
          <div class="payment-options"><label class="payment-option"><input type="radio" name="payment_method" value="FPX" checked><span><strong>FPX Online Banking</strong><span>Pay securely with your bank account</span></span></label><label class="payment-option"><input type="radio" name="payment_method" value="Card"><span><strong>Credit or debit card</strong><span>Visa, Mastercard and supported cards</span></span></label><label class="payment-option"><input type="radio" name="payment_method" value="E-Wallet"><span><strong>E-Wallet</strong><span>Pay using your preferred e-wallet</span></span></label></div>
          <button class="pay-button" type="submit">Confirm &amp; pay RM <?= e($total) ?></button>
        </form>
        <a class="back-link" href="book_reservation.php">← Back to edit reservation</a><p class="notice">After payment, your receipt will be available to save or print for collection day.</p>
      </aside>
    </div>
  </main>
</body>
</html>
