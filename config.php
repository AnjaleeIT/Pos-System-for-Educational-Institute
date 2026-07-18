<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "pos";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 'coordinator') {
    $checkStatus = $conn->query("SELECT system_status FROM system_control WHERE id = 1")->fetch_assoc();
    if ($checkStatus && $checkStatus['system_status'] == 'blocked') {
        session_unset();
        session_destroy();
        header("Location: index.php?error=system_blocked");
        exit;
    }
}
?>