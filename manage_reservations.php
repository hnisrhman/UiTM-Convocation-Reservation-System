<?php
session_start();
include 'db_connect.php';
include 'header_admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login_admin.php");
    exit();
}

$successMsg = "";

// Update status form handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $res_id = $_POST['res_id'];
    $new_status = $_POST['status'];
    mysqli_query($conn, "UPDATE reservations SET status='$new_status' WHERE id='$res_id'");
    $successMsg = "Status updated successfully!";
}

$result = mysqli_query($conn, "SELECT * FROM reservations ORDER BY id DESC");

echo "<h2>Manage Reservations</h2>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='8'>
    <tr>
      <th>ID</th>
      <th>User ID</th>
      <th>Robe Type</th>
      <th>Robe Size</th>
      <th>Cap</th>
      <th>Hood Code</th>
      <th>Total</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Action</th>
    </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['user_id']}</td>
            <td>{$row['robe_type']}</td>
            <td>{$row['robe_size']}</td>
            <td>{$row['graduation_cap']}</td>
            <td>{$row['hood_code']}</td>
            <td>RM {$row['total_price']}</td>
            <td>{$row['status']}</td>
            <td>{$row['payment_status']}</td>
            <td>
                <form method='post' style='display:inline-block;'>
                    <input type='hidden' name='res_id' value='{$row['id']}'>
                    <select name='status' required>
                        <option value='Pending' ".($row['status']=='Pending'?'selected':'').">Pending</option>
                        <option value='Done' ".($row['status']=='Done'?'selected':'').">Done</option>
                        <option value='Rejected' ".($row['status']=='Rejected'?'selected':'').">Rejected</option>
                    </select>
                    <button type='submit' name='update_status'>Update</button>
                </form>
                <a href='edit_reservation_admin.php?id={$row['id']}'>Edit</a>";

        echo "</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No reservations found.</p>";
}

// If status updated successfully, show popup
if (!empty($successMsg)) {
    echo "<script>alert('$successMsg'); window.location='manage_reservations.php';</script>";
}
?>
