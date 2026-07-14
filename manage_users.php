<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login_admin.php"); exit(); }
include 'header_admin.php';
include 'db_connect.php';

$result = mysqli_query($conn, "SELECT * FROM users");

echo "<h2>Manage Users</h2>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['id']}</td><td>{$row['full_name']}</td><td>{$row['email']}</td><td>{$row['role']}</td></tr>";
}
echo "</table>";
?>
