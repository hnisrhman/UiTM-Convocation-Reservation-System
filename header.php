<?php 
//session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
<title>UiTM Convocation System</title>
<link rel="stylesheet" href="css/style.css">
<script src="validation.js"></script>
</head>
<body>
<header>
<h1>UiTM Convocation Reservation System</h1>
<p>Student Records and Convocation Division (BRPK)</p>

<nav>
  <?php if ($currentPage == 'login_user.php'): ?>
    <a href="index.php">Home</a>
    <a href="login_user.php">Sign In</a>
    <a href="register.php">Sign Up</a>

  <?php elseif ($currentPage == 'login_admin.php'): ?>
    <a href="index.php">Home</a>

  <?php else: ?>
    <!-- Default header for other pages -->
    <?php if (isset($_SESSION['user_id'])): ?>
      <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="index.php">Home</a>
        <a href="dashboard_admin.php">Dashboard</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="index.php">Home</a>
        <a href="dashboard_user.php">Dashboard</a>
        <a href="view_reservation.php">My Reservations</a>
        <a href="logout.php">Logout</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="index.php">Home</a>
      <a href="login_user.php">Sign In</a>
      <a href="register.php">Sign Up</a>
    <?php endif; ?>
  <?php endif; ?>
</nav>
</header>