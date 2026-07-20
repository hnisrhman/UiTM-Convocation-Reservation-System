<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login_user.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reserve Attire | UiTM RobeReserve</title>
  <style>
    :root { --navy: #062b55; --navy-dark: #031d3b; --gold: #f6bf19; --ink: #18273a; --muted: #607086; --line: #d9e2ec; --page: #f2f6fa; --surface: #fff; }
    * { box-sizing: border-box; }
    body { margin: 0; color: var(--ink); background: var(--page); font-family: "Segoe UI", Arial, sans-serif; }
    .site-header { background: var(--navy-dark); color: #fff; border-bottom: 4px solid var(--gold); }
    .header-inner { width: min(1200px, calc(100% - 40px)); min-height: 86px; margin: auto; display: flex; align-items: center; gap: 18px; }
    .brand { display: flex; align-items: center; gap: 13px; margin-right: auto; color: #fff; text-decoration: none; }
    .brand img { width: 49px; height: 49px; padding: 4px; object-fit: contain; background: #fff; border-radius: 8px; }
    .brand strong { display: block; font-size: 1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }
    nav { display: flex; gap: 3px; align-items: center; } nav a { padding: 9px 11px; border-radius: 6px; color: #e7eff8; font-size: .94rem; font-weight: 650; text-decoration: none; }
    nav a:hover, nav a:focus-visible, nav a.active { color: var(--gold); background: rgba(255,255,255,.09); outline: none; }.logout { border: 1px solid rgba(255,255,255,.28); }
    main { width: min(1080px, calc(100% - 40px)); margin: auto; padding: 42px 0 72px; }
    .page-heading { max-width: 700px; margin-bottom: 28px; }.eyebrow { margin: 0 0 8px; color: #8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    h1 { margin: 0; color: var(--navy); font-size: clamp(2rem,4vw,2.65rem); line-height:1.15; }.page-heading > p:last-child { margin: 12px 0 0; color: var(--muted); font-size: 1.06rem; line-height: 1.6; }
    .booking-layout { display:grid; grid-template-columns:minmax(0, 1.5fr) minmax(275px, .75fr); align-items:start; gap:24px; }.form-card, .summary-card { padding:clamp(24px,4vw,38px); background:var(--surface); border-radius:14px; box-shadow:0 8px 22px rgba(22,48,77,.09); }
    .form-section + .form-section { margin-top: 31px; padding-top: 28px; border-top: 1px solid var(--line); }.form-section h2, .summary-card h2 { margin:0 0 5px; color:var(--navy); font-size:1.27rem; }.section-note { margin:0 0 20px; color:var(--muted); line-height:1.5; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }.field.full { grid-column:1 / -1; }label { display:block; margin-bottom:7px; color:#34465d; font-size:.92rem; font-weight:750; } .required { color:#b33920; }
    input, select { width:100%; min-height:46px; padding:10px 12px; color:var(--ink); background:#fff; border:1px solid #bfcbd8; border-radius:7px; font:inherit; transition:border-color .15s, box-shadow .15s; }input:focus, select:focus { border-color:#2563a4; box-shadow:0 0 0 3px rgba(37,99,164,.13); outline:none; }.field-help { margin:7px 0 0; color:var(--muted); font-size:.82rem; line-height:1.4; }
    .package-option { display:flex; align-items:flex-start; gap:12px; padding:16px; border:1px solid var(--line); border-radius:9px; background:#f7fafc; cursor:pointer; }.package-option:has(input:checked) { border-color:var(--gold); background:#fff8de; }.package-option input { width:19px; min-height:19px; margin:2px 0 0; accent-color:var(--navy); }.package-option strong { display:block; color:var(--navy); }.package-option span { display:block; margin-top:4px; color:var(--muted); font-size:.88rem; line-height:1.45; }
    .continue { width:100%; margin-top:28px; min-height:49px; border:0; border-radius:8px; color:var(--navy-dark); background:var(--gold); font:inherit; font-weight:800; cursor:pointer; }.continue:hover, .continue:focus-visible { background:#ffcf42; outline:none; }
    .summary-card { position:sticky; top:22px; border-top:5px solid var(--gold); }.summary-card > p { margin:0 0 23px; color:var(--muted); line-height:1.5; }.summary-list { margin:0; padding:0; list-style:none; }.summary-list li { display:flex; justify-content:space-between; gap:16px; padding:13px 0; border-top:1px solid var(--line); color:var(--muted); font-size:.94rem; }.summary-list li strong { color:var(--ink); text-align:right; }.total-row { display:flex; justify-content:space-between; align-items:end; margin-top:10px; padding-top:20px; border-top:2px solid var(--navy); }.total-row span { color:var(--muted); font-size:.88rem; font-weight:750; }.total-row strong { color:var(--navy); font-size:1.7rem; }.secure-note { margin:22px 0 0; padding:13px; border-radius:7px; color:#3b5974; background:#eef5fb; font-size:.84rem; line-height:1.45; }
    @media (max-width:850px) { .header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; }.booking-layout { grid-template-columns:1fr; }.summary-card { position:static; }.form-card { order:1; }.summary-card { order:2; } }
    @media (max-width:560px) { .header-inner, main { width:min(100% - 28px,1080px); }main { padding-top:28px; }.form-grid { grid-template-columns:1fr; }.field.full { grid-column:auto; }.form-card, .summary-card { border-radius:11px; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner">
    <a class="brand" href="dashboard_user.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Convocation Reservation System</span></span></a>
    <nav aria-label="User navigation"><a href="dashboard_user.php">Dashboard</a><a class="active" href="book_reservation.php">Reserve Now</a><a href="view_reservation.php">My Reservation</a><a href="profile.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav>
  </div></header>
  <main>
    <section class="page-heading"><p class="eyebrow">Step 1 of 2 · Attire details</p><h1>Reserve your convocation attire</h1><p>Choose your robe and accessories below. You will review the reservation and select a payment method on the next page.</p></section>
    <div class="booking-layout">
      <form class="form-card" method="post" action="confirm_reservation.php" id="reservationForm">
        <section class="form-section">
          <h2>Choose your attire</h2><p class="section-note">Select the items that match your graduation requirements.</p>
          <div class="form-grid">
            <div class="field"><label for="robeType">Robe type <span class="required">*</span></label><select id="robeType" name="robe_type" required><option value="">Select robe type</option><option value="Diploma" data-price="15">Diploma — RM15</option><option value="Degree" data-price="25">Degree — RM25</option><option value="Master" data-price="35">Master — RM35</option><option value="PhD" data-price="40">PhD — RM40</option></select></div>
            <div class="field"><label for="robeSize">Robe size <span class="required">*</span></label><select id="robeSize" name="robe_size" required><option value="">Select your size</option><option>XS</option><option>S</option><option>M</option><option>L</option><option>XL</option><option>XXL</option></select></div>
            <div class="field"><label for="gradCap">Graduation cap <span class="required">*</span></label><select id="gradCap" name="graduation_cap" required><option value="">Select a cap</option><option value="Mortar Board" data-price="10">Mortar Board — RM10</option><option value="Bonnet" data-price="15">Bonnet — RM15</option></select></div>
            <div class="field"><label for="hoodCode">Hood programme code</label><input type="text" id="hoodCode" name="hood_code" placeholder="For example: CS240 or BM770" maxlength="20"><p class="field-help">A hood is RM10 and is optional if not required for your programme.</p></div>
          </div>
        </section>
        <section class="form-section">
          <h2>Collection details</h2><p class="section-note">Choose your preferred date for attire collection.</p>
          <div class="form-grid"><div class="field full"><label for="collectionDate">Collection date <span class="required">*</span></label><input type="date" id="collectionDate" name="collection_date" required></div></div>
        </section>
        <section class="form-section">
          <h2>Package option</h2><p class="section-note">Save with a set package containing your robe, hood and cap.</p>
          <label class="package-option" for="oneSet"><input type="checkbox" id="oneSet"><span><strong>Choose a complete set package</strong><span>Diploma RM20 · Degree RM40 · Master with Mortar Board RM50 · Master with Bonnet RM55 · PhD RM60</span></span></label>
        </section>
        <input type="hidden" id="totalPrice" name="total_price" value="0.00">
        <input type="hidden" id="packageSelected" name="package_selected" value="0">
        <button class="continue" type="submit">Review reservation &amp; continue</button>
      </form>
      <aside class="summary-card" aria-live="polite"><h2>Reservation summary</h2><p>Your estimate updates as you make selections.</p><ul class="summary-list"><li><span>Robe</span><strong id="summaryRobe">Not selected</strong></li><li><span>Cap</span><strong id="summaryCap">Not selected</strong></li><li><span>Hood</span><strong id="summaryHood">Not added</strong></li><li><span>Package</span><strong id="summaryPackage">Standard pricing</strong></li></ul><div class="total-row"><span>Estimated total</span><strong id="totalDisplay">RM 0.00</strong></div><p class="secure-note">Your reservation is not confirmed until you review the details and complete payment on the next page.</p></aside>
    </div>
  </main>
  <script>
    const robeType = document.getElementById('robeType'), cap = document.getElementById('gradCap'), hood = document.getElementById('hoodCode'), packageOption = document.getElementById('oneSet');
    const priceFor = (select) => Number(select.options[select.selectedIndex]?.dataset.price || 0);
    function packagePrice(robe, capValue) {
      if (robe === 'Diploma') return 20;
      if (robe === 'Degree') return 40;
      if (robe === 'PhD') return 60;
      if (robe === 'Master' && capValue === 'Mortar Board') return 50;
      if (robe === 'Master' && capValue === 'Bonnet') return 55;
      return 0;
    }
    function updateSummary() {
      const robe = robeType.value, capValue = cap.value, hoodValue = hood.value.trim();
      let total = priceFor(robeType) + priceFor(cap) + (hoodValue ? 10 : 0);
      const setPrice = packagePrice(robe, capValue);
      if (packageOption.checked && setPrice) total = setPrice;
      document.getElementById('totalPrice').value = total.toFixed(2);
      document.getElementById('packageSelected').value = packageOption.checked ? '1' : '0';
      document.getElementById('totalDisplay').textContent = `RM ${total.toFixed(2)}`;
      document.getElementById('summaryRobe').textContent = robe || 'Not selected';
      document.getElementById('summaryCap').textContent = capValue || 'Not selected';
      document.getElementById('summaryHood').textContent = hoodValue || 'Not added';
      document.getElementById('summaryPackage').textContent = packageOption.checked ? (setPrice ? `Complete set — RM ${setPrice}.00` : (robe === 'Master' ? 'Select a cap for the Master’s set' : 'Select a robe first')) : 'Standard pricing';
    }
    [robeType, cap, hood, packageOption].forEach((element) => element.addEventListener(element === hood ? 'input' : 'change', updateSummary));
    document.getElementById('collectionDate').min = new Date().toISOString().split('T')[0];
    document.getElementById('reservationForm').addEventListener('submit', (event) => { updateSummary(); if (!robeType.value || !cap.value) { event.preventDefault(); alert('Please select a robe type and graduation cap before continuing.'); } });
  </script>
</body>
</html>
