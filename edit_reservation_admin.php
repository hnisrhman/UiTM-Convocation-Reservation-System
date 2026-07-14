<?php
session_start();
include 'db_connect.php';
include 'header_admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login_admin.php");
    exit();
}

$id = $_GET['id'];

// Fetch reservation data
$res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM reservations WHERE id='$id'"));

if (!$res) {
    echo "<p>Reservation not found.</p>";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $robe_type = $_POST['robe_type'];
    $robe_size = $_POST['robe_size'];
    $graduation_cap = $_POST['graduation_cap'];
    $hood_code = $_POST['hood_code'];
    $collection_date = $_POST['collection_date'];
    $total_price = $_POST['total_price'];

    mysqli_query($conn, "UPDATE reservations SET 
        robe_type='$robe_type',
        robe_size='$robe_size',
        graduation_cap='$graduation_cap',
        hood_code='$hood_code',
        collection_date='$collection_date',
        total_price='$total_price'
        WHERE id='$id'");

    echo "<script>alert('Reservation updated successfully!'); window.location='manage_reservations.php';</script>";
    exit();
}
?>

<h2>Edit Reservation (Admin)</h2>

<form method="post">
  Robe Type:
  <select name="robe_type" required>
    <option value="Diploma" <?= $res['robe_type']=='Diploma'?'selected':'' ?>>Diploma</option>
    <option value="Degree" <?= $res['robe_type']=='Degree'?'selected':'' ?>>Degree</option>
    <option value="Master" <?= $res['robe_type']=='Master'?'selected':'' ?>>Master</option>
    <option value="PhD" <?= $res['robe_type']=='PhD'?'selected':'' ?>>PhD</option>
  </select><br>

  Robe Size:
  <select name="robe_size">
    <option value="">-- Select --</option>
    <?php
    $sizes = ['XS','S','M','L','XL','XXL'];
    foreach ($sizes as $size) {
        $selected = $res['robe_size']==$size ? 'selected' : '';
        echo "<option value='$size' $selected>$size</option>";
    }
    ?>
  </select><br>

  Graduation Cap:
  <select name="graduation_cap">
    <option value="Mortar Board" <?= $res['graduation_cap']=='Mortar Board'?'selected':'' ?>>Mortar Board</option>
    <option value="Bonnet" <?= $res['graduation_cap']=='Bonnet'?'selected':'' ?>>Bonnet</option>
  </select><br>

  Hood Code:
  <input type="text" name="hood_code" value="<?= $res['hood_code'] ?>"><br>

  Collection Date:
  <input type="date" name="collection_date" value="<?= $res['collection_date'] ?>"><br>

  Total Price (RM):
  <input type="text" name="total_price" value="<?= $res['total_price'] ?>" required><br>

  <button type="submit">Update Reservation</button>
</form>
