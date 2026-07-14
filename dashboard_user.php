<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') { header("Location: login_user.php"); exit(); }
include 'header_user.php';
include 'db_connect.php';
?>

<div style="text-align:center; margin-bottom:20px;">
  <h2>Welcome to your Reservation Dashboard</h2>
  <p>Here you can view available attire products, check your booking details, and manage your convocation attire reservation.</p>
  <p>Please confirm your booking early and make payment online to secure your attire collection on the convocation day.</p>
</div>

<h2 style="text-align:center;">Hood Color Reference by Program</h2>

<div style="text-align: center; margin: 20px 0;">
  <img src="images/hood_sample.jpg" alt="Hood Sample" width="600">
  <p style="margin-top: 5px;"><em>Sample Hood Photo</em></p>
</div>

<h2 style="text-align:center;">Available Attire & Packages</h2>

<div style="text-align:center;">
<?php
$result = mysqli_query($conn, "SELECT * FROM products");
while ($product = mysqli_fetch_assoc($result)) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px; display:inline-block; text-align:center;'>";
    echo "<img src='{$product['image_path']}' width='150'><br>";
    echo "<strong>{$product['product_name']}</strong><br>";
    echo "{$product['description']}<br>";
    echo "Price: RM {$product['price']}<br>";
    echo "<a href='book_reservation.php' class='button-link'>Book Now</a>";
    echo "</div>";
}
?>
</div>
