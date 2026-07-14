<?php
session_start();
include 'db_connect.php';
include 'header_user.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login_user.php");
    exit();
}

// On final confirm + insert into DB
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method'])) {
    $uid = $_SESSION['user_id'];
    $robe = $_POST['robe_type'];
    $size = $_POST['robe_size'];
    $cap = $_POST['graduation_cap'];
    $hood = $_POST['hood_code'];
    $date = $_POST['collection_date'];
    $price = $_POST['total_price'];
    $payment_ref = "PAY" . strtoupper(uniqid());

    mysqli_query($conn, "INSERT INTO reservations 
        (user_id, robe_size, robe_type , graduation_cap, hood_code, collection_date, total_price, payment_status, payment_ref)
        VALUES
        ('$uid', '$size', '$robe' , '$cap', '$hood', '$date', '$price', 'Paid', '$payment_ref')");

    $id = mysqli_insert_id($conn);

    echo "<script>alert('Payment successful via $payment_method! Payment Ref: $payment_ref'); window.location='print_receipt.php?id=$id';</script>";
    exit();
}

// If first time landed via POST from book_reservation.php
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['robe_type'])) {
    header("Location: book_reservation.php");
    exit();
}
?>

<h2>Confirm Reservation</h2>
<p>Robe: <?= $_POST['robe_type'] ?> (<?= $_POST['robe_size'] ?>)</p>
<p>Cap: <?= $_POST['graduation_cap'] ?></p>
<p>Hood: <?= $_POST['hood_code'] ?></p>
<p>Collection Date: <?= $_POST['collection_date'] ?></p>
<p>Total: RM <?= $_POST['total_price'] ?></p>

<form method="post">
  <input type="hidden" name="robe_type" value="<?= $_POST['robe_type'] ?>">
  <input type="hidden" name="robe_size" value="<?= $_POST['robe_size'] ?>">
  <input type="hidden" name="graduation_cap" value="<?= $_POST['graduation_cap'] ?>">
  <input type="hidden" name="hood_code" value="<?= $_POST['hood_code'] ?>">
  <input type="hidden" name="collection_date" value="<?= $_POST['collection_date'] ?>">
  <input type="hidden" name="total_price" value="<?= $_POST['total_price'] ?>">

  Payment Method:
  <select name="payment_method" required>
    <option value="FPX">FPX</option>
    <option value="Card">Credit Card</option>
    <option value="E-Wallet">E-Wallet</option>
  </select>
  <button type="submit">Confirm & Proceed</button>
</form>
