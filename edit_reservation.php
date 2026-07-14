<?php
session_start();
include 'db_connect.php';
include 'header_user.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login_user.php");
    exit();
}

$id = $_GET['id'];
$res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM reservations WHERE id='$id' AND user_id='{$_SESSION['user_id']}'"));

if (!$res) {
    echo "<p>Invalid reservation.</p>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $robe = $_POST['robe_type'];
    $size = $_POST['robe_size'];
    $cap = $_POST['graduation_cap'];
    $hood = $_POST['hood_code'];
    $date = $_POST['collection_date'];
    $price = $_POST['total_price'];

    mysqli_query($conn, "UPDATE reservations SET 
        robe_type='$robe', 
        robe_size='$size', 
        graduation_cap='$cap', 
        hood_code='$hood', 
        collection_date='$date',
        total_price='$price'
        WHERE id='$id'");

    echo "<script>alert('Reservation updated successfully.'); window.location='view_reservation.php';</script>";
}
?>

<h2>Edit Reservation</h2>
<form method="post">
  Robe Type:
  <select name="robe_type" id="robeType" onchange="updatePrice()" required>
    <option value="Diploma" <?= $res['robe_type']=='Diploma'?'selected':'' ?>>Diploma (RM15)</option>
    <option value="Degree" <?= $res['robe_type']=='Degree'?'selected':'' ?>>Degree (RM25)</option>
    <option value="Master" <?= $res['robe_type']=='Master'?'selected':'' ?>>Master (RM35)</option>
    <option value="PhD" <?= $res['robe_type']=='PhD'?'selected':'' ?>>PhD (RM40)</option>
  </select><br><br>

  Robe Size:
  <select name="robe_size">
    <option <?= $res['robe_size']=='XS'?'selected':'' ?>>XS</option>
    <option <?= $res['robe_size']=='S'?'selected':'' ?>>S</option>
    <option <?= $res['robe_size']=='M'?'selected':'' ?>>M</option>
    <option <?= $res['robe_size']=='L'?'selected':'' ?>>L</option>
    <option <?= $res['robe_size']=='XL'?'selected':'' ?>>XL</option>
    <option <?= $res['robe_size']=='XXL'?'selected':'' ?>>XXL</option>
  </select><br><br>

  Graduation Cap:
  <select name="graduation_cap" id="gradCap" onchange="updatePrice()" required>
    <option value="Mortar Board" <?= $res['graduation_cap']=='Mortar Board'?'selected':'' ?>>Mortar Board (RM10)</option>
    <option value="Bonnet" <?= $res['graduation_cap']=='Bonnet'?'selected':'' ?>>Bonnet (RM15)</option>
  </select><br><br>

  Hood Code:
  <input type="text" name="hood_code" value="<?= $res['hood_code'] ?>"><br><br>

  Collection Date:
  <input type="date" name="collection_date" value="<?= $res['collection_date'] ?>" required><br><br>

  Total Price (RM):
  <input type="text" id="totalPrice" name="total_price" readonly value="<?= $res['total_price'] ?>"><br><br>

  <button type="submit">Save Changes</button>
</form>

<script>
function updatePrice() {
    var robePrice = 0;
    var capPrice = 0;

    var robeType = document.getElementById('robeType').value;
    var gradCap = document.getElementById('gradCap').value;

    if (robeType === 'Diploma') robePrice = 15;
    else if (robeType === 'Degree') robePrice = 25;
    else if (robeType === 'Master') robePrice = 35;
    else if (robeType === 'PhD') robePrice = 40;

    if (gradCap === 'Mortar Board') capPrice = 10;
    else if (gradCap === 'Bonnet') capPrice = 15;

    var total = robePrice + capPrice + 10; // +10 for Hood
    document.getElementById('totalPrice').value = total.toFixed(2);
}
</script>
