<?php
session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us | UiTM RobeReserve</title>
  <style>
    :root { --navy: #062b55; --navy-dark: #031d3b; --gold: #f6bf19; --ink: #172438; --muted: #526278; }
    * { box-sizing: border-box; }
    body { margin: 0; color: var(--ink); font-family: "Segoe UI", Arial, sans-serif; background: #e7edf3; }
    .site-header { background: var(--navy-dark); color: #fff; border-bottom: 4px solid var(--gold); }
    .header-inner { width: min(1180px, calc(100% - 40px)); min-height: 132px; margin: auto; display: flex; align-items: center; gap: 28px; }
    .logo { width: 126px; height: 100px; object-fit: contain; padding: 7px; background: #fff; border-radius: 10px; }
    .brand { flex: 1; }
    .brand h1 { margin: 0; font-size: clamp(1.45rem, 2.4vw, 2rem); line-height: 1.15; }
    .brand p { margin: 7px 0 0; color: #d8e5f3; font-size: 1rem; }
    nav { display: flex; align-items: center; gap: 6px; }
    nav a, .dropbtn { padding: 11px 13px; color: #fff; background: transparent; border: 0; border-radius: 6px; font: inherit; font-weight: 650; text-decoration: none; cursor: pointer; white-space: nowrap; }
    nav a:hover, .dropbtn:hover, nav a:focus-visible, .dropbtn:focus-visible { color: var(--gold); background: rgba(255,255,255,.09); outline: none; }
    .register-link { margin-left: 7px; color: var(--navy); background: var(--gold); }
    .register-link:hover, .register-link:focus-visible { color: var(--navy); background: #ffcf42; }
    .dropdown { position: relative; }
    .dropdown-content { display: none; position: absolute; z-index: 10; top: calc(100% + 7px); right: 0; min-width: 180px; padding: 7px; background: #fff; border-radius: 8px; box-shadow: 0 12px 28px rgba(0,20,48,.24); }
    .dropdown-content.open { display: block; }
    .dropdown-content a { display: block; color: var(--navy); }
    .dropdown-content a:hover, .dropdown-content a:focus-visible { color: var(--navy); background: #fff3c6; }
    main { min-height: calc(100vh - 136px); padding: clamp(42px, 7vw, 82px) 20px; background: linear-gradient(rgba(231,237,243,.91), rgba(231,237,243,.96)), url('images/bg_convo.jpg') center / cover fixed; }
    .page-intro { width: min(1080px, 100%); margin: 0 auto 28px; text-align: center; }
    .eyebrow { margin: 0 0 9px; color: #8b6505; font-size: .8rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    h2 { margin: 0; color: var(--navy); font-size: clamp(2rem, 4vw, 2.75rem); line-height: 1.15; }
    .page-intro > p:last-child { max-width: 720px; margin: 15px auto 0; color: var(--muted); font-size: 1.08rem; line-height: 1.65; }
    .content-grid { width: min(1080px, 100%); margin: auto; display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(270px, .8fr); gap: 24px; align-items: start; }
    .card { padding: clamp(26px, 4vw, 42px); background: rgba(255,255,255,.97); border-radius: 14px; box-shadow: 0 16px 36px rgba(12,37,66,.14); }
    .card h3 { margin: 0 0 17px; color: var(--navy); font-size: 1.42rem; }
    .card p { margin: 0 0 19px; color: #3e4d61; font-size: 1.06rem; line-height: 1.7; }
    .card p:last-child { margin-bottom: 0; }
    .contact-card { border-top: 5px solid var(--gold); }
    .contact-card h3 { margin-bottom: 20px; }
    address { margin: 0; color: #3e4d61; font-size: 1rem; font-style: normal; line-height: 1.65; }
    address strong { display: block; margin-bottom: 7px; color: var(--ink); font-size: 1.05rem; }
    .phone { display: inline-flex; gap: 8px; margin-top: 24px; padding-top: 20px; width: 100%; border-top: 1px solid #dce4ed; color: var(--navy); font-weight: 700; text-decoration: none; }
    .phone:hover { color: #8b6505; }
    @media (max-width: 850px) { .header-inner { min-height: auto; padding: 18px 0; flex-wrap: wrap; gap: 15px; } .logo { width: 90px; height: 75px; } .brand { min-width: 0; } nav { width: 100%; flex-wrap: wrap; } .content-grid { grid-template-columns: 1fr; } }
    @media (max-width: 480px) { .header-inner { width: min(100% - 28px, 1180px); } .brand p { font-size: .88rem; } nav a, .dropbtn { padding: 9px 8px; } .register-link { margin-left: 0; } .card { border-radius: 11px; } }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <img src="images/logo_uitm.png" alt="Universiti Teknologi MARA" class="logo">
      <div class="brand">
        <h1>UiTM Convocation Reservation System</h1>
        <p>Student Records and Convocation Division (BRPK)</p>
      </div>
      <nav aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <div class="dropdown">
          <button type="button" class="dropbtn" id="loginButton" aria-expanded="false" aria-controls="loginDropdown">Login ▾</button>
          <div id="loginDropdown" class="dropdown-content">
            <a href="login_user.php">Login as User</a>
            <a href="login_admin.php">Login as Admin</a>
          </div>
        </div>
        <a href="register.php" class="register-link">Register</a>
      </nav>
    </div>
  </header>
  <main>
    <section class="page-intro" aria-labelledby="about-heading">
      <p class="eyebrow">UiTM RobeReserve</p>
      <h2 id="about-heading">Supporting a memorable graduation day</h2>
      <p>Everything graduates need to reserve their official convocation attire, clearly organised in one convenient place.</p>
    </section>
    <div class="content-grid">
      <section class="card" aria-labelledby="our-role-heading">
        <h3 id="our-role-heading">About Us</h3>
        <p>The <strong>Student Records and Convocation Division (BRPK)</strong> manages and coordinates convocation-related activities at Universiti Teknologi MARA (UiTM), including student record verification and robe reservations for graduates.</p>
        <p>RobeReserve simplifies booking academic attire for upcoming convocation ceremonies. Diploma, Degree, Master’s and PhD graduates can reserve the correct robe, hood, mortarboard or bonnet conveniently and securely.</p>
      </section>
      <aside class="card contact-card" aria-labelledby="contact-heading">
        <h3 id="contact-heading">Contact Us</h3>
        <address>
          <strong>Student Records and Convocation Division (BRPK)</strong>
          Registrar's Office<br>
          Level 3, Menara Sultan Abdul Aziz Shah (SAAS)<br>
          Universiti Teknologi MARA (UiTM)<br>
          40450 Shah Alam, Selangor Darul Ehsan<br>
          Malaysia
        </address>
        <a class="phone" href="tel:+60355443131"><span>Tel:</span> +603-5544 3131</a>
      </aside>
    </div>
  </main>
  <script>
    const loginButton = document.getElementById('loginButton');
    const loginDropdown = document.getElementById('loginDropdown');
    loginButton.addEventListener('click', () => {
      const isOpen = loginDropdown.classList.toggle('open');
      loginButton.setAttribute('aria-expanded', isOpen);
    });
    document.addEventListener('click', (event) => {
      if (!event.target.closest('.dropdown')) {
        loginDropdown.classList.remove('open');
        loginButton.setAttribute('aria-expanded', 'false');
      }
    });
  </script>
</body>
</html>
