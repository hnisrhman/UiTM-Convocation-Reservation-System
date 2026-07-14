<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login_user.php"); exit(); }
include 'db_connect.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM reservations WHERE id='$id'");
header("Location: view_reservation.php");
?>