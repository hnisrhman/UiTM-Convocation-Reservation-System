<?php
session_start();
include 'header.php';
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND role='user'");
    $user = mysqli_fetch_assoc($result);

    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard_user.php");
        exit();
    } else {
        echo "<script>alert('Invalid Email or Password!'); window.location='login_user.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>UiTM Convocation System</title>
<style>
form p a {
    color: #2196F3;
    text-decoration: none;
    font-size: 14px;
}

form p a:hover {
    text-decoration: underline;
}

</style>
</head>
<body>
    <h2>User Login</h2>
    <form method="post">
    <label>Email:</label><input type="email" name="email" required><br>
    <label>Password:</label><input type="password" name="password" required><br>
    <button type="submit">Login</button>
    <p><a href="forgot_password.php">Forgot Password?</a></p>
    </form>
</body>

