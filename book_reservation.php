<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') { header("Location: login_user.php"); exit(); }
include 'header_user.php';
include 'db_connect.php';
?>

<html>
  <head>
    <title>Book Your Convocation Attire</title>
    <script src="price.js"></script>
  </head>
  <body>
    <h2>Book Your Convocation Attire</h2>

    <form method="post" action="confirm_reservation.php" onsubmit="return calculateTotal();">
      Robe Type:
      <select id="robeType" name="robe_type" onchange="updateTotal()">
        <option value="">-- Select --</option>
        <option value="Diploma">Diploma (RM15)</option>
        <option value="Degree">Degree (RM25)</option>
        <option value="Master">Master (RM35)</option>
        <option value="PhD">PhD (RM40)</option>
      </select><br>

      Robe Size:
      <select name="robe_size">
        <option value="">-- Select --</option>
        <option>XS</option><option>S</option><option>M</option>
        <option>L</option><option>XL</option><option>XXL</option>
      </select><br>

      Graduation Cap:
      <select id="gradCap" name="graduation_cap" onchange="updateTotal()">
        <option value="">-- Select --</option>
        <option value="Mortar Board">Mortar Board (RM10)</option>
        <option value="Bonnet">Bonnet (RM15)</option>
      </select><br>

      Hood Program Code:
      <input type="text" id="hoodCode" name="hood_code" placeholder="e.g. CS240, BM770" onchange="updateTotal()"><br>

      <input type="checkbox" id="oneSet" onchange="toggleOneSet()">One Set Package (Special Price)<br>

      Total Price: <input type="text" id="totalPrice" name="total_price" readonly><br>

      Collection Date:
      <input type="date" name="collection_date" required><br>

      <button type="submit">Confirm Reservation</button>
    </form>

  </body>
</html>

