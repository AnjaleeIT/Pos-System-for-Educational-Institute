<?php
// external_bill.php
session_start();
if(!isset($_SESSION['role']) || ($_SESSION['role'] != 'coordinator' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php");
    exit;
}
require 'config.php';

$payment_id = intval($_GET['id'] ?? 0);
if ($payment_id <= 0) die("Invalid bill id.");

// Fetch payment row
$stmt = $conn->prepare("SELECT payment_id, student_id, payment_type, category, paid_amount, balance, payment_date FROM payments WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$res = $stmt->get_result();
$payment = $res->fetch_assoc();
$stmt->close();

if (!$payment) die("Payment not found.");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>External Payment Bill #<?=htmlspecialchars($payment['payment_id'])?></title>
  <style>
    body{font-family:Arial; background:#f4f6f8; padding:30px;}
    .bill{max-width:520px; margin:30px auto; background:#fff; padding:22px; border-radius:8px; box-shadow:0 6px 20px rgba(0,0,0,0.06);}
    h2{margin:0 0 10px 0;}
    .row{display:flex; justify-content:space-between; padding:6px 0;}
    .muted{color:#666; font-size:13px;}
    .print{display:inline-block; margin-top:18px; padding:10px 14px; background:#28a745; color:#fff; border-radius:6px; text-decoration:none;}
  </style>
</head>
<body>
  <div class="bill">
    <h2>Payment Receipt</h2>
    <div class="muted">Bill ID: <?=htmlspecialchars($payment['payment_id'])?></div>
    <hr>
    <div class="row"><strong>Type</strong><span><?=htmlspecialchars($payment['payment_type'])?> / <?=htmlspecialchars($payment['category'])?></span></div>
    <div class="row"><strong>Amount Paid</strong><span>Rs. <?=number_format($payment['paid_amount'],2)?></span></div>
    <div class="row"><strong>Remaining Balance</strong><span>Rs. <?=number_format($payment['balance'],2)?></span></div>
    <div class="row"><strong>Date</strong><span><?=htmlspecialchars($payment['payment_date'])?></span></div>
    <hr>
    <p class="muted">This is a system-generated receipt. Thank you.</p>
    <a class="print" href="#" onclick="window.print();return false;">Print Receipt</a>
  </div>
</body>
</html>
