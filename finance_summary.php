<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

require 'config.php';

// Default: current month
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Fetch daily totals for the selected month
$dailyData = [];
$dailyLabels = [];

$query = "SELECT DATE(payment_date) AS date, SUM(paid_amount) AS total
          FROM payments
          WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?
          GROUP BY DATE(payment_date)
          ORDER BY DATE(payment_date)";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $dailyLabels[] = $row['date'];
    $dailyData[] = $row['total'];
}

// Fetch overall totals (for the month)
$totalQuery = "SELECT 
    SUM(paid_amount) AS totalPaid,
    COUNT(payment_id) AS totalTransactions,
    SUM(balance) AS totalBalance
    FROM payments
    WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?";
$stmt2 = $conn->prepare($totalQuery);
$stmt2->bind_param("s", $selected_month);
$stmt2->execute();
$totalResult = $stmt2->get_result();
$totals = $totalResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Finance Summary (Admin)</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.container {
    max-width: 950px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.chart-container {
    position: relative;
    height: 400px;
    width: 100%;
}
.stat-box {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.stat {
    background-color: #007bff;
    color: white;
    padding: 20px;
    border-radius: 10px;
    width: 30%;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.stat h3 {
    margin: 0;
    font-size: 22px;
}
.stat p {
    margin: 5px 0 0;
    font-size: 16px;
}
form {
    text-align: center;
    margin-bottom: 20px;
}
input[type="month"] {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    padding: 10px 20px;
    margin-left: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #0056b3;
}
.back-btn {
    margin-top: 25px;
    background: #28a745;
}
.back-btn:hover {
    background: #1f7a31;
}
</style>
</head>
<body>
<header>Finance Summary (Admin View)</header>

<div class="container">
    <form method="POST">
        <label><strong>Select Month:</strong></label>
        <input type="month" name="month" value="<?php echo $selected_month; ?>" required>
        <button type="submit">View Report</button>
    </form>

    <div class="stat-box">
        <div class="stat">
            <h3>Rs. <?php echo number_format($totals['totalPaid'],2); ?></h3>
            <p>Total Payments (<?php echo $selected_month; ?>)</p>
        </div>
        <div class="stat">
            <h3><?php echo $totals['totalTransactions']; ?></h3>
            <p>Total Transactions</p>
        </div>
        <div class="stat">
            <h3>Rs. <?php echo number_format($totals['totalBalance'],2); ?></h3>
            <p>Total Remaining Balances</p>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="dailyChart"></canvas>
    </div>

    <button class="back-btn" onclick="window.location.href='admin_dashboard.php'">Back to Dashboard</button>
</div>

<script>
const ctx = document.getElementById('dailyChart');
const labels = <?php echo json_encode($dailyLabels); ?>;
const data = <?php echo json_encode($dailyData); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Daily Payments (Rs)',
            data: data,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            borderWidth: 2,
            tension: 0.3,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Daily Payments in ' + "<?php echo $selected_month; ?>",
                font: { size: 18 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Total Amount (Rs)' }
            },
            x: {
                title: { display: true, text: 'Date' }
            }
        }
    }
});
</script>
</body>
</html>
