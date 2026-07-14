<?php
include 'header.php';
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // plain text — no hash

    // Always set new user role to 'user'
    $sql = "INSERT INTO users (full_name, email, password, role) 
            VALUES ('$name', '$email', '$password', 'user')";

    if (mysqli_query($conn, $sql)) {
        echo "<p>Registration successful. <a href='login_user.php'>Login here</a></p>";
    } else {
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form method="post">
        <label>Name:</label><input type="text" name="full_name" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Password:</label><input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
