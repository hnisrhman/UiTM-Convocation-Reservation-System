<?php
session_start();
include 'db_connect.php';
include 'header_user.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login_user.php");
    exit();
}

$uid = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT * FROM reservations WHERE user_id='$uid' ORDER BY id DESC");

echo "<h2>Your Reservations</h2>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='8'>
    <tr>
        <th>ID</th><th>Robe</th><th>Size</th><th>Cap</th><th>Hood</th>
        <th>Collection Date</th><th>Price (RM)</th><th>Status</th><th>Payment</th><th>Actions</th>
    </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['robe_type']}</td>
            <td>{$row['robe_size']}</td>
            <td>{$row['graduation_cap']}</td>
            <td>{$row['hood_code']}</td>
            <td>{$row['collection_date']}</td>
            <td>{$row['total_price']}</td>
            <td>{$row['status']}</td>
            <td>{$row['payment_status']}";
        if ($row['payment_status'] == 'Unpaid') {
            echo " | <a href='make_payment.php?id={$row['id']}'>Pay</a>";
        } else {
            echo " | <a href='print_receipt.php?id={$row['id']}'>Receipt</a>";
        }
        echo "</td>
            <td>
                <a href='edit_reservation.php?id={$row['id']}'>Edit</a> | 
                <a href='cancel_reservation.php?id={$row['id']}' onclick=\"return confirm('Cancel this reservation?');\">Cancel</a>
            </td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No reservations made yet.</p>";
}
?>
