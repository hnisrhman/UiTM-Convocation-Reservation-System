<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login_user.php");
    exit();
}

$id = $_GET['id'];

// Fetch reservation data
$res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM reservations WHERE id='$id' AND user_id='{$_SESSION['user_id']}'"));

if (!$res) {
    echo "<p>Invalid reservation.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reservation Receipt</title>
    <style>
        .receipt {
            width: 700px;
            margin: 30px auto;
            padding: 20px;
            border: 2px dashed #333;
            background: #fff;
        }
        .receipt h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt p {
            font-size: 16px;
            margin: 8px 0;
        }
        .receipt strong {
            color: #002147;
        }
        .print-btn {
            margin: 20px auto;
            display: block;
            padding: 10px 20px;
            background: #002147;
            color: #ffc107;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #ffc107;
            color: #002147;
        }
    </style>
</head>
<body>

<div class="receipt">
    <h2>UiTM Convocation Reservation Receipt</h2>
    <p><strong>Reservation ID:</strong> <?= $res['id'] ?></p>
    <p><strong>Robe:</strong> <?= $res['robe_type'] ?> (Size: <?= $res['robe_size'] ?>)</p>
    <p><strong>Graduation Cap:</strong> <?= $res['graduation_cap'] ?></p>
    <p><strong>Hood Code:</strong> <?= $res['hood_code'] ?></p>
    <p><strong>Collection Date:</strong> <?= $res['collection_date'] ?></p>
    <p><strong>Total Paid:</strong> RM <?= $res['total_price'] ?></p>
    <p><strong>Payment Status:</strong> <?= $res['payment_status'] ?></p>
    <p><strong>Payment Reference:</strong> <?= $res['payment_ref'] ?></p>

    <p style="margin-top:20px;"><em>Please present this receipt at the convocation attire collection counter on your collection date. Thank you and congratulations!</em></p>
</div>

<button class="print-btn" onclick="window.print()">Print Receipt</button>

<button class="print-btn" onclick="window.location.href='dashboard_user.php'">Back to Dashboard</button>

</body>
</html>
