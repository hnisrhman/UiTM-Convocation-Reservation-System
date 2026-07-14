<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login_user.php");
    exit();
}

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM reservations WHERE id='$id' AND user_id='{$_SESSION['user_id']}'");

echo "<script>alert('Reservation cancelled.'); window.location='view_reservation.php';</script>";
?>
