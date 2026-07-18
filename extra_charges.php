<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}
require 'config.php';

// Handle form submission
if (isset($_POST['generate'])) {
    $student_id = trim($_POST['student_id']);
    $reason = trim($_POST['reason']);
    $amount = floatval($_POST['amount']);

    // Insert into database
    $sql = "INSERT INTO extra_charges (student_id, description, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $student_id, $reason, $amount);
    $stmt->execute();
    $charge_id = $conn->insert_id;

    // Redirect to bill page
    header("Location: extra_bill.php?id=$charge_id&student=$student_id&reason=$reason&amount=$amount");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Extra Charges</title>
<link rel="stylesheet" href="style.css">
<style>
label {
    display:block;
    margin-top:10px;
    font-weight:bold;
}
</style>
</head>
<body>
<header>Extra Charges</header>

<div class="container">
<form method="POST" action="">
    <h3>Enter Extra Charge Details</h3>

    <label>Student ID:</label>
    <input type="text" name="student_id" placeholder="Enter Student ID" required>

    <label>Reason for Extra Charge:</label>
    <input type="text" name="reason" placeholder="e.g. Late Fee, Lost ID" required>

    <label>Amount to Pay:</label>
    <input type="number" step="0.01" name="amount" placeholder="Enter Amount" required>

    <button type="submit" name="generate">Generate Bill</button>
</form>
</div>

</body>
</html>
