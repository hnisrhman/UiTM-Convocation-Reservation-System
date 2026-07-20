<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UiTM Convocation Reservation System</title>
  <style>
    :root {
      --uitm-navy: #062b55;
      --uitm-navy-dark: #031d3b;
      --uitm-gold: #f6bf19;
      --ink: #152235;
      --muted: #506075;
      --surface: #ffffff;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      color: var(--ink);
      font-family: "Segoe UI", Arial, sans-serif;
      background: #e8edf3;
    }

    .site-header {
      background: var(--uitm-navy-dark);
      color: #fff;
      border-bottom: 4px solid var(--uitm-gold);
    }

    .header-inner {
      width: min(1180px, calc(100% - 40px));
      min-height: 132px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 28px;
    }

    .logo {
      width: 126px;
      height: 100px;
      object-fit: contain;
      border-radius: 10px;
      background: #fff;
      padding: 7px;
    }

    .brand { flex: 1; }

    .brand h1 {
      margin: 0;
      font-size: clamp(1.45rem, 2.4vw, 2rem);
      line-height: 1.15;
      letter-spacing: .01em;
    }

    .brand p {
      margin: 7px 0 0;
      color: #d8e5f3;
      font-size: 1rem;
    }

    nav { display: flex; align-items: center; gap: 6px; }

    nav a, .dropbtn {
      color: #fff;
      font: inherit;
      font-weight: 650;
      text-decoration: none;
      background: transparent;
      border: 0;
      cursor: pointer;
      padding: 11px 13px;
      border-radius: 6px;
      white-space: nowrap;
    }

    nav a:hover, .dropbtn:hover, nav a:focus-visible, .dropbtn:focus-visible {
      color: var(--uitm-gold);
      background: rgba(255,255,255,.09);
      outline: none;
    }

    .register-link {
      color: var(--uitm-navy);
      background: var(--uitm-gold);
      margin-left: 7px;
    }

    .register-link:hover, .register-link:focus-visible {
      color: var(--uitm-navy);
      background: #ffcf42;
    }

    .dropdown { position: relative; }

    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      top: calc(100% + 7px);
      z-index: 10;
      min-width: 180px;
      padding: 7px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 12px 28px rgba(0, 20, 48, .24);
    }

    .dropdown-content.open { display: block; }
    .dropdown-content a { display: block; color: var(--uitm-navy); }
    .dropdown-content a:hover, .dropdown-content a:focus-visible { color: var(--uitm-navy); background: #fff3c6; }

    .hero {
      position: relative;
      min-height: calc(100vh - 136px);
      padding: clamp(48px, 8vw, 100px) 20px;
      display: grid;
      place-items: center;
      background: linear-gradient(90deg, rgba(3, 29, 59, .76), rgba(3, 29, 59, .38)), url('images/bg_convo.jpg') center / cover no-repeat;
    }

    .intro-card {
      width: min(900px, 100%);
      padding: clamp(28px, 5vw, 54px);
      border-radius: 16px;
      background: rgba(255,255,255,.96);
      box-shadow: 0 22px 48px rgba(0, 19, 45, .32);
    }

    .eyebrow {
      margin: 0 0 10px;
      color: #8e6500;
      font-size: .8rem;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
    }

    h2 {
      margin: 0;
      color: var(--uitm-navy);
      font-size: clamp(1.75rem, 4vw, 2.55rem);
      line-height: 1.18;
    }

    .lead {
      max-width: 760px;
      margin: 20px 0 30px;
      color: var(--muted);
      font-size: clamp(1rem, 1.6vw, 1.15rem);
      line-height: 1.75;
    }

    .package {
      padding: 24px;
      border-left: 5px solid var(--uitm-gold);
      border-radius: 0 10px 10px 0;
      background: #f3f7fb;
    }

    .package h3 { margin: 0 0 13px; color: var(--uitm-navy); font-size: 1.18rem; }
    .package ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; }
    .package li { display: flex; align-items: start; gap: 10px; color: #34455c; line-height: 1.45; }
    .package li::before { content: "✓"; color: #8e6500; font-weight: 800; }

    .actions { margin-top: 30px; display: flex; flex-wrap: wrap; gap: 12px; }
    .button { display: inline-block; padding: 12px 18px; border-radius: 7px; background: var(--uitm-navy); color: #fff; font-weight: 700; text-decoration: none; }
    .button:hover, .button:focus-visible { background: #0b427d; }
    .button-secondary { background: transparent; color: var(--uitm-navy); box-shadow: inset 0 0 0 2px #b7c5d6; }
    .button-secondary:hover, .button-secondary:focus-visible { color: var(--uitm-navy); background: #edf3f9; }

    @media (max-width: 850px) {
      .header-inner { min-height: auto; padding: 18px 0; flex-wrap: wrap; gap: 15px; }
      .logo { width: 90px; height: 75px; }
      .brand { min-width: 0; }
      nav { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
    }

    @media (max-width: 480px) {
      .header-inner { width: min(100% - 28px, 1180px); }
      .brand p { font-size: .88rem; }
      nav a, .dropbtn { padding: 9px 8px; }
      .register-link { margin-left: 0; }
      .intro-card { border-radius: 12px; }
    }
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

  <main class="hero">
    <section class="intro-card" aria-labelledby="attire-heading">
      <p class="eyebrow">Graduation made simple</p>
      <h2 id="attire-heading">Reserve your UiTM convocation attire with confidence.</h2>
      <p class="lead">Book your official robe, hood, mortarboard or bonnet in one place. Choose a full package if needed, then complete your reservation before collection and confirm payment online.</p>
      <div class="package">
        <h3>Your package can include</h3>
        <ul>
          <li>Diploma, Degree, Master’s and PhD robes</li>
          <li>Mortarboard or bonnet</li>
          <li>Hood matched to your programme code</li>
        </ul>
      </div>
      <div class="actions">
        <a href="register.php" class="button">Create an account</a>
        <a href="about.php" class="button button-secondary">Learn more</a>
      </div>
    </section>
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
