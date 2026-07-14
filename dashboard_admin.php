<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login_admin.php"); exit(); }
include 'header_admin.php';
include 'db_connect.php';

$res_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations"))['total'];
$paid_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS paid FROM reservations WHERE payment_status='Paid'"))['paid'];
$total_profit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) AS profit FROM reservations WHERE payment_status='Paid'"))['profit'];

echo "<h2 style='text-align:center;'>Admin Dashboard</h2>";
echo "<p style='text-align:center;'>Total Reservations: $res_total</p>";
echo "<p style='text-align:center;'>Paid Reservations: $paid_res</p>";
echo "<p style='text-align:center;'>Total Profit: RM " . number_format($total_profit, 2) . "</p>";
?>

<h3 style="text-align:center;">Monthly Profit Chart</h3>
<canvas id="profitChart" width="700" height="300"></canvas>

<!-- Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// Fetch monthly profit data
$data = mysqli_query($conn, "SELECT DATE_FORMAT(collection_date, '%M') AS month, SUM(total_price) AS profit 
                             FROM reservations 
                             WHERE payment_status='Paid' 
                             GROUP BY month ORDER BY MIN(collection_date)");
$months = $profits = [];
while ($row = mysqli_fetch_assoc($data)) {
    $months[] = $row['month'];
    $profits[] = $row['profit'];
}
?>

<script>
var ctx = document.getElementById('profitChart').getContext('2d');
var profitChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Monthly Profit (RM)',
            data: <?= json_encode($profits) ?>,
            backgroundColor: '#ffc107',
            borderColor: '#002147',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
