<?php
session_start();
include 'header.php';
include 'db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND role='admin'");
    $user = mysqli_fetch_assoc($result);

    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "<script>alert('Invalid Email or Password!'); window.location='login_user.php';</script>";
    }
}
?>

<html>
    <head>
        <title>Admin Login</title>
    </head>
    <body>
        <h2>Admin Login</h2>
        <form method="post">
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Password:</label><input type="password" name="password" required><br>
        <button type="submit">Login</button>
        </form>
    </body>
</html>

