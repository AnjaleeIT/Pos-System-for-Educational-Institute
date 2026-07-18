<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $type = $_POST['type'];
    $amount = $_POST['new_payment'] ?? $_POST['payment'];

    $conn->query("INSERT INTO payments (student_id, type, amount, date) VALUES ('$student_id', '$type', '$amount', NOW())");

    echo "<h3>Bill Generated</h3>";
    echo "<p>Student ID: $student_id</p>";
    echo "<p>Payment Type: $type</p>";
    echo "<p>Amount Paid: Rs. $amount</p>";
    echo "<a href='coordinator_dashboard.php'>Back</a>";
}
?>
