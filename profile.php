<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login_user.php"); exit(); }
include 'header_user.php';
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $sql = "UPDATE users SET full_name='$name', email='$email' WHERE id='$user_id'";
    mysqli_query($conn, $sql);
    echo "<p>Profile updated.</p>";
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
?>

<html>
  <head>
    <title>My Profile</title>
  </head>
  <body>
    <h2>My Profile</h2>
    <form method="post">
      Name: <input type="text" name="full_name" value="<?= $user['full_name'] ?>"><br>
      Email: <input type="email" name="email" value="<?= $user['email'] ?>"><br>
      <button type="submit">Update</button>
    </form>
  </body>
</html>

