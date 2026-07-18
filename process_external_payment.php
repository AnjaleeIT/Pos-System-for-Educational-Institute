<?php
// process_external_payment.php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}
require 'config.php'; // assumes $conn is mysqli

$applicant_name = trim($_POST['applicant_name'] ?? '');
$nic = trim($_POST['nic'] ?? '');
$contact_no = trim($_POST['contact_no'] ?? '');
$service_label = trim($_POST['service_label'] ?? 'Other');
$amount = floatval($_POST['amount'] ?? 0);
$paid_amount = floatval($_POST['paid_amount'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? 'Cash');

if ($service_label === '' || $amount <= 0 || $paid_amount < 0) {
    die("Invalid input. Please go back and provide correct values.");
}

$balance = $amount - $paid_amount;
$payment_type = 'External';
$category = 'External'; // keeps category generic; you can use $service_label if you altered ENUM

// Start transaction
$conn->begin_transaction();
try {
    // Insert into payments
    $stmt = $conn->prepare("INSERT INTO payments (student_id, payment_type, category, paid_amount, balance, payment_date) VALUES (NULL, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdd", $payment_type, $category, $paid_amount, $balance);
    $stmt->execute();
    $payment_id = $stmt->insert_id;
    $stmt->close();

    // Save reason/details into extra_charges (or into a new external table)
    if ($conn->query("SHOW TABLES LIKE 'external_payments'")->num_rows > 0) {
        $stmt2 = $conn->prepare("INSERT INTO external_payments (applicant_name, nic, contact_no, service_type, amount, paid_amount, payment_method, payment_date, collected_by) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $collected_by = $_SESSION['username'] ?? $_SESSION['role'];
        $stmt2->bind_param("sssddds", $applicant_name, $nic, $contact_no, $service_label, $amount, $paid_amount, $payment_method, $collected_by);
        $stmt2->execute();
        $stmt2->close();
    } else {
        // fallback: insert into extra_charges table (description + amount)
        $desc = $service_label . ($applicant_name ? " - " . $applicant_name : "");
        $stmt3 = $conn->prepare("INSERT INTO extra_charges (student_id, description, amount, charge_date) VALUES (NULL, ?, ? , NOW())");
        $stmt3->bind_param("sd", $desc, $amount);
        $stmt3->execute();
        $stmt3->close();
    }

    $conn->commit();

    // Redirect to bill page
    header("Location: external_bill.php?id=" . intval($payment_id));
    exit;
} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage());
    die("Database error: please contact admin.");
}
