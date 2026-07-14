<?php
include 'db_connect.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass == $confirm_pass) {
        mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE email='$email'");
        echo "<script>alert('Password reset successful.'); window.location='login_user.php';</script>";
    } else {
        echo "<script>alert('Passwords do not match.');</script>";
    }
}
?>

<html>
    <head>
        <tittle>Reset Password</tittle>
    </head>
    <body>
        <h2>Reset Password</h2>
        <form method="post">
        Email: <input type="email" name="email" required><br>
        New Password: <input type="password" name="new_password" required><br>
        Confirm Password: <input type="password" name="confirm_password" required><br>
        <button type="submit">Reset Password</button>
        </form>
    </body>
</html>

