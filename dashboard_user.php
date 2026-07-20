<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login_user.php');
    exit();
}
include 'db_connect.php';

$userId = mysqli_real_escape_string($conn, (string) $_SESSION['user_id']);
$userResult = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '$userId' LIMIT 1");
$user = $userResult ? mysqli_fetch_assoc($userResult) : null;
$firstName = trim(explode(' ', $user['full_name'] ?? 'Graduate')[0]);
$reservationResult = mysqli_query($conn, "SELECT * FROM reservations WHERE user_id = '$userId' ORDER BY id DESC LIMIT 1");
$reservation = $reservationResult ? mysqli_fetch_assoc($reservationResult) : null;
$productResult = mysqli_query($conn, 'SELECT * FROM products ORDER BY id ASC');
$productsByType = ['package' => [], 'robe' => [], 'hood' => [], 'cap' => []];
if ($productResult) {
    while ($product = mysqli_fetch_assoc($productResult)) {
        $type = strtolower($product['product_type'] ?? '');
        if (isset($productsByType[$type])) $productsByType[$type][] = $product;
    }
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Dashboard | UiTM RobeReserve</title>
  <style>
    :root { --navy: #062b55; --navy-dark: #031d3b; --gold: #f6bf19; --ink: #18273a; --muted: #627187; --line: #dce4ed; --surface: #fff; --page: #f2f6fa; --success: #20744a; --warning: #a86b00; }
    * { box-sizing: border-box; }
    body { margin: 0; color: var(--ink); background: var(--page); font-family: "Segoe UI", Arial, sans-serif; }
    .site-header { background: var(--navy-dark); color: #fff; border-bottom: 4px solid var(--gold); }
    .header-inner { width: min(1200px, calc(100% - 40px)); min-height: 86px; margin: auto; display: flex; align-items: center; gap: 18px; }
    .brand { display: flex; align-items: center; gap: 13px; margin-right: auto; text-decoration: none; color: #fff; }
    .brand img { width: 49px; height: 49px; padding: 4px; object-fit: contain; background: #fff; border-radius: 8px; }
    .brand strong { display: block; font-size: 1.08rem; }
    .brand span { display: block; margin-top: 2px; color: #cbd9e7; font-size: .8rem; }
    nav { display: flex; align-items: center; gap: 3px; }
    nav a { padding: 9px 11px; color: #e7eff8; border-radius: 6px; font-size: .94rem; font-weight: 650; text-decoration: none; }
    nav a:hover, nav a:focus-visible, nav a.active { color: var(--gold); background: rgba(255,255,255,.09); outline: none; }
    .logout { border: 1px solid rgba(255,255,255,.28); }
    main { width: min(1200px, calc(100% - 40px)); margin: 0 auto; padding: 42px 0 70px; }
    .welcome { position: relative; overflow: hidden; padding: clamp(28px, 5vw, 48px); border-radius: 16px; color: #fff; background: linear-gradient(112deg, #062b55 0%, #0b4b85 100%); box-shadow: 0 14px 30px rgba(7,42,81,.2); }
    .welcome::after { content: ""; position: absolute; width: 300px; height: 300px; right: -100px; top: -160px; border: 50px solid rgba(246,191,25,.17); border-radius: 50%; }
    .welcome-content { position: relative; z-index: 1; max-width: 720px; }
    .eyebrow { margin: 0 0 8px; color: #ffdb68; font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    h1 { margin: 0; font-size: clamp(1.85rem, 4vw, 2.7rem); line-height: 1.14; }
    .welcome p:last-of-type { max-width: 650px; margin: 14px 0 24px; color: #dbe8f5; font-size: 1.05rem; line-height: 1.65; }
    .button { display: inline-block; padding: 12px 17px; border-radius: 7px; background: var(--gold); color: var(--navy-dark); font-weight: 800; text-decoration: none; }
    .button:hover, .button:focus-visible { background: #ffcf42; outline: none; }
    .button-outline { margin-left: 8px; color: #fff; background: transparent; box-shadow: inset 0 0 0 1px rgba(255,255,255,.55); }
    .button-outline:hover, .button-outline:focus-visible { color: #fff; background: rgba(255,255,255,.12); }
    .section-heading { display: flex; justify-content: space-between; align-items: end; gap: 15px; margin: 42px 0 17px; }
    h2 { margin: 0; color: var(--navy); font-size: 1.45rem; }
    .section-heading p { margin: 5px 0 0; color: var(--muted); }
    .text-link { color: var(--navy); font-weight: 750; text-decoration: none; }
    .text-link:hover { color: #9b6b00; }
    .reservation-card { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 25px; padding: 28px; background: var(--surface); border-radius: 13px; box-shadow: 0 7px 20px rgba(22,48,77,.08); }
    .reservation-title { display: flex; flex-wrap: wrap; align-items: center; gap: 11px; }
    .reservation-title h3 { margin: 0; color: var(--navy); font-size: 1.25rem; }
    .badge { display: inline-block; padding: 5px 9px; border-radius: 20px; background: #fff1cf; color: var(--warning); font-size: .78rem; font-weight: 800; }
    .badge.paid, .badge.confirmed { color: var(--success); background: #e0f5e8; }
    .reservation-info { margin: 14px 0 0; display: flex; flex-wrap: wrap; gap: 11px 24px; color: var(--muted); line-height: 1.5; }
    .reservation-info strong { display: block; color: #36475d; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; }
    .empty-note { margin: 8px 0 0; color: var(--muted); line-height: 1.55; }
    .small-button { display: inline-block; padding: 10px 14px; border-radius: 7px; background: var(--navy); color: #fff; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .small-button:hover { background: #0b4b85; }
    .guide { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px; }
    .guide-card { overflow: hidden; display: grid; grid-template-columns: 170px 1fr; min-height: 178px; background: #fff; border-radius: 13px; box-shadow: 0 7px 20px rgba(22,48,77,.08); }
    .guide-card > img { width: 100%; height: 100%; object-fit: cover; }
    .image-button { position: relative; display: block; min-height: 178px; padding: 0; overflow: hidden; border: 0; background: #eef3f8; cursor: zoom-in; }
    .image-button img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .image-button span { position: absolute; right: 9px; bottom: 9px; padding: 5px 8px; border-radius: 5px; color: #fff; background: rgba(3,29,59,.84); font-size: .75rem; font-weight: 750; }
    .image-button:hover span, .image-button:focus-visible span { background: var(--navy); }
    .guide-content { padding: 23px; }
    .guide-content h3 { margin: 0 0 8px; color: var(--navy); font-size: 1.15rem; }
    .guide-content p { margin: 0; color: var(--muted); line-height: 1.55; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 19px; }
    .catalog-group + .catalog-group { margin-top: 34px; }
    .catalog-heading { display: flex; align-items: center; gap: 12px; margin: 0 0 15px; color: var(--navy); font-size: 1.15rem; }
    .catalog-heading::after { content: ""; height: 1px; flex: 1; background: #cfdbe7; }
    .catalog-heading span { padding: 4px 8px; border-radius: 20px; color: #7b5d0b; background: #fff0bf; font-size: .75rem; font-weight: 800; }
    .product-card { overflow: hidden; display: flex; flex-direction: column; background: #fff; border: 1px solid #e3eaf2; border-radius: 12px; transition: transform .18s ease, box-shadow .18s ease; }
    .product-card:hover { transform: translateY(-4px); box-shadow: 0 13px 24px rgba(22,48,77,.13); }
    .product-image { width: 100%; height: 175px; object-fit: contain; padding: 14px; background: #f5f8fb; }
    .product-content { display: flex; flex: 1; flex-direction: column; padding: 18px; }
    .product-type { margin: 0 0 5px; color: #8a6505; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .product-card h3 { margin: 0; color: var(--navy); font-size: 1.1rem; }
    .product-card p { margin: 9px 0 17px; color: var(--muted); font-size: .92rem; line-height: 1.45; }
    .product-footer { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: auto; }
    .price { color: var(--navy); font-size: 1.05rem; font-weight: 800; }
    .book-link { padding: 8px 11px; color: var(--navy); border-radius: 6px; background: #fff0bf; font-size: .86rem; font-weight: 800; text-decoration: none; }
    .book-link:hover { background: var(--gold); }
    @media (max-width: 880px) { .header-inner { min-height: auto; padding: 15px 0; flex-wrap: wrap; } nav { width: 100%; overflow-x: auto; } .guide { grid-template-columns: 1fr; } }
    .image-modal { position: fixed; inset: 0; z-index: 20; display: none; place-items: center; padding: 28px; background: rgba(1,16,33,.86); }
    .image-modal.open { display: grid; }
    .image-modal-content { position: relative; max-width: min(100%, 1100px); max-height: 100%; }
    .image-modal img { display: block; max-width: 100%; max-height: calc(100vh - 56px); border-radius: 7px; box-shadow: 0 18px 55px rgba(0,0,0,.45); }
    .close-modal { position: absolute; top: -13px; right: -13px; width: 36px; height: 36px; border: 0; border-radius: 50%; color: #fff; background: var(--navy); font-size: 1.4rem; line-height: 1; cursor: pointer; }
    @media (max-width: 620px) { .header-inner, main { width: min(100% - 28px, 1200px); } main { padding-top: 28px; } .welcome { border-radius: 12px; } .button { display: block; text-align: center; } .button-outline { margin: 10px 0 0; } .reservation-card { grid-template-columns: 1fr; padding: 23px; } .small-button { width: fit-content; } .section-heading { align-items: start; flex-direction: column; margin-top: 33px; } .guide-card { grid-template-columns: 1fr; } .guide-card > img, .image-button { height: 175px; min-height: 175px; } }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <a class="brand" href="dashboard_user.php">
        <img src="images/logo_uitm.png" alt="UiTM">
        <span><strong>UiTM RobeReserve</strong><span>Convocation Reservation System</span></span>
      </a>
      <nav aria-label="User navigation">
        <a class="active" href="dashboard_user.php">Dashboard</a>
        <a href="book_reservation.php">Reserve Now</a>
        <a href="view_reservation.php">My Reservation</a>
        <a href="profile.php">Profile</a>
        <a class="logout" href="logout.php">Logout</a>
      </nav>
    </div>
  </header>
  <main>
    <section class="welcome" aria-labelledby="dashboard-title">
      <div class="welcome-content">
        <p class="eyebrow">Your graduation, organised</p>
        <h1 id="dashboard-title">Welcome back, <?= e($firstName) ?>.</h1>
        <p>Manage your convocation attire in one place. Reserve early to make collection day smooth and stress-free.</p>
        <a href="book_reservation.php" class="button">Reserve attire</a>
        <a href="view_reservation.php" class="button button-outline">View my reservation</a>
      </div>
    </section>

    <div class="section-heading">
      <div><h2>Your latest reservation</h2><p>Keep track of your reservation and payment progress.</p></div>
      <a class="text-link" href="view_reservation.php">View all reservations →</a>
    </div>
    <section class="reservation-card">
      <?php if ($reservation): ?>
        <div>
          <div class="reservation-title">
            <h3><?= e($reservation['robe_type']) ?> attire</h3>
            <span class="badge <?= strtolower(e($reservation['payment_status'])) ?>"><?= e($reservation['payment_status']) ?></span>
            <span class="badge <?= strtolower(e($reservation['status'])) ?>"><?= e($reservation['status']) ?></span>
          </div>
          <div class="reservation-info">
            <span><strong>Collection date</strong><?= e($reservation['collection_date']) ?></span>
            <span><strong>Size</strong><?= e($reservation['robe_size']) ?></span>
            <span><strong>Total</strong>RM <?= e($reservation['total_price']) ?></span>
          </div>
        </div>
        <a href="view_reservation.php" class="small-button">Manage booking</a>
      <?php else: ?>
        <div><div class="reservation-title"><h3>No reservation yet</h3></div><p class="empty-note">Choose your robe and accessories now to get ready for your convocation ceremony.</p></div>
        <a href="book_reservation.php" class="small-button">Start reservation</a>
      <?php endif; ?>
    </section>

    <div class="guide">
      <section class="guide-card">
        <button type="button" class="image-button" data-full-image="images/hood_sample.jpg" data-image-alt="UiTM hood colour reference chart" aria-label="Open full-size hood colour reference">
          <img src="images/hood_sample.jpg" alt="UiTM hood colour reference chart"><span>View full size</span>
        </button>
        <div class="guide-content"><h3>Hood colour reference</h3><p>Your hood is determined by your programme code. Keep your programme information ready while booking.</p></div>
      </section>
      <section class="guide-card">
        <img src="images/bg_convo.jpg" alt="UiTM graduates celebrating convocation">
        <div class="guide-content"><h3>Before you collect</h3><p>Confirm your reservation early and complete online payment to secure your attire for collection day.</p></div>
      </section>
    </div>

    <div class="section-heading">
      <div><h2>Available attire &amp; packages</h2><p>Select the items you need for your convocation ceremony.</p></div>
      <a class="text-link" href="book_reservation.php">Make a reservation →</a>
    </div>
    <section aria-label="Available attire products">
      <?php
      $catalogSections = [
          'package' => ['Complete set packages', 'All-in-one attire options'],
          'robe' => ['Graduation robes', 'Choose your qualification level'],
          'hood' => ['Hoods', 'Programme-specific accessories'],
          'cap' => ['Graduation caps', 'Finish your convocation attire']
      ];
      $hasProducts = false;
      foreach ($catalogSections as $type => [$title, $subtitle]):
        if (empty($productsByType[$type])) continue;
        $hasProducts = true;
      ?>
        <section class="catalog-group" aria-labelledby="<?= e($type) ?>-heading">
          <h3 class="catalog-heading" id="<?= e($type) ?>-heading"><?= e($title) ?> <span><?= e($subtitle) ?></span></h3>
          <div class="product-grid">
            <?php foreach ($productsByType[$type] as $product): ?>
              <article class="product-card">
                <img class="product-image" src="<?= e($product['image_path']) ?>" alt="<?= e($product['product_name']) ?>">
                <div class="product-content">
                  <p class="product-type"><?= e($product['product_type']) ?></p>
                  <h3><?= e($product['product_name']) ?></h3>
                  <p><?= e($product['description']) ?></p>
                  <div class="product-footer"><span class="price">RM <?= e($product['price']) ?></span><a class="book-link" href="book_reservation.php">Reserve</a></div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
      <?php if (!$hasProducts): ?><p>Attire options are currently unavailable. Please try again shortly.</p><?php endif; ?>
    </section>
  </main>
  <div class="image-modal" id="imageModal" role="dialog" aria-modal="true" aria-label="Full-size hood colour reference">
    <div class="image-modal-content"><button type="button" class="close-modal" aria-label="Close image viewer">×</button><img id="modalImage" src="" alt=""></div>
  </div>
  <script>
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModal = () => imageModal.classList.remove('open');
    document.querySelectorAll('[data-full-image]').forEach((button) => {
      button.addEventListener('click', () => {
        modalImage.src = button.dataset.fullImage;
        modalImage.alt = button.dataset.imageAlt;
        imageModal.classList.add('open');
      });
    });
    imageModal.addEventListener('click', (event) => { if (event.target === imageModal) closeModal(); });
    document.querySelector('.close-modal').addEventListener('click', closeModal);
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });
  </script>
</body>
</html>
