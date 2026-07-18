<?php
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
<title>School Payment Bill</title>
<link rel="stylesheet" href="style.css">
<style>
.bill-container {
    max-width: 600px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.print-btn {
    display:block;
    width:100%;
    padding:12px;
    font-size:16px;
    background:#007bff;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
.print-btn:hover {background:#0056b3;}
</style>
</head>
<body>
<header>Generated Bill</header>

<div class="bill-container">
    <h2>School Payment Receipt</h2>
    <p><b>Date:</b> <?php echo date("Y-m-d H:i:s"); ?></p>
    <hr>
    <p><b>Student ID:</b> <?php echo htmlspecialchars($_GET['student']); ?></p>
    <p><b>Grade:</b> <?php echo htmlspecialchars($_GET['grade']); ?></p>
    <p><b>Subject:</b> <?php echo htmlspecialchars($_GET['subject']); ?></p>
    <p><b>Paid Amount:</b> Rs. <?php echo htmlspecialchars($_GET['pay']); ?></p>
    <p><b>Remaining Payment:</b> Rs. <?php echo htmlspecialchars($_GET['remaining']); ?></p>
    <p><b>Balance:</b> Rs. <?php echo htmlspecialchars($_GET['balance']); ?></p>
    <hr>
    <p><b>Payment ID:</b> <?php echo htmlspecialchars($_GET['id']); ?></p>
    <p><b>Processed By:</b> <?php echo htmlspecialchars($_SESSION['username']); ?></p>

    <button class="print-btn" onclick="window.print()">Print Bill</button>
    <br>
    <button class="print-btn" style="background:#28a745;" onclick="window.location.href='coordinator_dashboard.php'">Back</button>
</div>
</body>
</html>
