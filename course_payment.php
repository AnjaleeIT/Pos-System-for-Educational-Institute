<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

require 'config.php';
include 'system_status_check.php'; // shows block banner if system blocked

// Load course payment values
$courses = [];
$res = $conn->query("SELECT name, amount FROM payment_settings WHERE type='course'");
while ($row = $res->fetch_assoc()) {
    $courses[$row['name']] = $row['amount'];
}

// Handle form submission
if (isset($_POST['generate'])) {
    $student_id = trim($_POST['student_id']);
    $course = trim($_POST['course']);
    $start_month = trim($_POST['start_month']);
    $months = intval($_POST['months']);
    $haveCash = floatval($_POST['haveCash']);
    $payCash = floatval($_POST['payCash']);
    $remaining = floatval($_POST['remaining']);
    $balance = $haveCash - $payCash;

    $sql = "INSERT INTO payments (student_id, payment_type, category, paid_amount, balance)
            VALUES (?, 'Internal', 'Course', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idd", $student_id, $payCash, $balance);
    $stmt->execute();
    $payment_id = $conn->insert_id;

    header("Location: course_bill.php?id=$payment_id&student=$student_id&course=$course&months=$months&start=$start_month&have=$haveCash&pay=$payCash&balance=$balance&remaining=$remaining");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Course Payment</title>
<link rel="stylesheet" href="style.css">
<style>
label {display:block;margin-top:10px;font-weight:bold;}
</style>
<script>
function updatePayment() {
    let sel = document.getElementById("course");
    let amount = sel.options[sel.selectedIndex].getAttribute("data-amount");
    if (amount) document.getElementById("remaining").value = amount;
}

function calculateValues() {
    let x = parseFloat(document.getElementById("haveCash").value) || 0;
    let y = parseFloat(document.getElementById("payCash").value) || 0;
    let remaining = parseFloat(document.getElementById("remaining").value) || 0;
    let balance = x - y;
    let newRemaining = remaining - y;
    document.getElementById("balance").value = balance.toFixed(2);
    document.getElementById("remaining").value = newRemaining.toFixed(2);
}
</script>
</head>
<body>
<header>Course Payment</header>

<div class="container">
<form method="POST">
    <h3>Student Course Payment</h3>

    <label>Student ID:</label>
    <input type="text" name="student_id" placeholder="Enter Student ID" required>

    <label>Select Course:</label>
    <select name="course" id="course" onchange="updatePayment()" required>
        <option value="">Select Course</option>
        <?php
        foreach ($courses as $name => $amount) {
            echo "<option value='$name' data-amount='$amount'>$name (Rs. $amount)</option>";
        }
        ?>
    </select>

    <label>Starting Month:</label>
    <input type="month" name="start_month" required>

    <label>Number of Months:</label>
    <input type="number" name="months" min="1" required>

    <label>Course Total Fee / Remaining:</label>
    <input type="number" step="0.01" id="remaining" name="remaining" readonly required>

    <label>Current Cash (X):</label>
    <input type="number" step="0.01" id="haveCash" name="haveCash" oninput="calculateValues()" required>

    <label>Paying Now (Y):</label>
    <input type="number" step="0.01" id="payCash" name="payCash" oninput="calculateValues()" required>

    <label>Balance (X - Y):</label>
    <input type="text" id="balance" name="balance" readonly>

    <button type="submit" name="generate">Generate Bill</button>
</form>
</div>
</body>
</html>
