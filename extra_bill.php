<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Extra Charge Bill</title>
<link rel="stylesheet" href="style.css">
<style>
.bill-container {
    max-width:600px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}
.bill-header {
    text-align:center;
    margin-bottom:20px;
}
.bill-header h2 {margin:0;}
.print-btn {
    display:block;
    width:100%;
    padding:12px;
    font-size:16px;
    background-color:#007bff;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
.print-btn:hover {background-color:#0056b3;}
</style>
</head>
<body>
<header>Generated Bill</header>

<div class="bill-container">
    <div class="bill-header">
        <h2>Extra Charge Receipt</h2>
        <p><b>Date:</b> <?php echo date("Y-m-d H:i:s"); ?></p>
    </div>

    <p><b>Student ID:</b> <?php echo htmlspecialchars($_GET['student']); ?></p>
    <p><b>Reason:</b> <?php echo htmlspecialchars($_GET['reason']); ?></p>
    <p><b>Amount:</b> Rs. <?php echo htmlspecialchars($_GET['amount']); ?></p>
    <hr>
    <p><b>Charge ID:</b> <?php echo htmlspecialchars($_GET['id']); ?></p>
    <p><b>Processed By:</b> <?php echo htmlspecialchars($_SESSION['username']); ?></p>

    <button class="print-btn" onclick="window.print()">Print Bill</button>
    <br>
    <button class="print-btn" style="background:#28a745;" onclick="window.location.href='coordinator_dashboard.php'">Back to Dashboard</button>
</div>
</body>
</html>
