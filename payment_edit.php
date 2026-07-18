<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'coordinator' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php");
    exit;
}

require 'config.php';
include 'system_status_check.php'; // will block edits if system is closed

$message = "";

// Update payment if form submitted
if (isset($_POST['update'])) {
    $id = intval($_POST['payment_id']);
    $paid = floatval($_POST['paid_amount']);
    $balance = floatval($_POST['balance']);

    $update = $conn->prepare("UPDATE payments SET paid_amount=?, balance=? WHERE payment_id=?");
    $update->bind_param("ddi", $paid, $balance, $id);
    $update->execute();
    $message = "✅ Payment record updated successfully!";
}

// Search filters
$where = "1";
$params = [];
$types = "";

if (isset($_GET['student_id']) && $_GET['student_id'] != "") {
    $where .= " AND student_id = ?";
    $params[] = $_GET['student_id'];
    $types .= "i";
}

if (isset($_GET['date']) && $_GET['date'] != "") {
    $where .= " AND DATE(payment_date) = ?";
    $params[] = $_GET['date'];
    $types .= "s";
}

if (isset($_GET['category']) && $_GET['category'] != "") {
    $where .= " AND category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

// Build query
$sql = "SELECT * FROM payments WHERE $where ORDER BY payment_date DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Search & Edit</title>
<link rel="stylesheet" href="style.css">
<style>
.container {
    max-width: 1000px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
form label {
    font-weight: bold;
    margin-right: 10px;
}
form input, form select {
    margin-right: 10px;
    padding: 5px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
table, th, td { border: 1px solid #ddd; }
th { background-color: #007bff; color: white; }
td, th { padding: 8px; text-align: center; }
button, input[type=submit] {
    padding: 6px 15px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover, input[type=submit]:hover {
    background: #1f7a31;
}
.message {
    font-weight: bold;
    color: green;
}
</style>
</head>
<body>
<header>Search & Edit Payments</header>

<div class="container">
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <form method="GET" style="margin-bottom:20px;">
        <label>Student ID:</label>
        <input type="text" name="student_id" value="<?php echo $_GET['student_id'] ?? ''; ?>">

        <label>Date:</label>
        <input type="date" name="date" value="<?php echo $_GET['date'] ?? ''; ?>">

        <label>Category:</label>
        <select name="category">
            <option value="">All</option>
            <option value="Course" <?php if(($_GET['category'] ?? '')=="Course") echo 'selected'; ?>>Course</option>
            <option value="School" <?php if(($_GET['category'] ?? '')=="School") echo 'selected'; ?>>School</option>
            <option value="Extra Charges" <?php if(($_GET['category'] ?? '')=="Extra Charges") echo 'selected'; ?>>Extra Charges</option>
        </select>

        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='coordinator_dashboard.php'">Back</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Student ID</th>
            <th>Category</th>
            <th>Paid (Rs)</th>
            <th>Balance (Rs)</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <form method='POST'>
                        <td>{$row['payment_id']}</td>
                        <td>{$row['student_id']}</td>
                        <td>{$row['category']}</td>
                        <td><input type='number' step='0.01' name='paid_amount' value='{$row['paid_amount']}'></td>
                        <td><input type='number' step='0.01' name='balance' value='{$row['balance']}'></td>
                        <td>{$row['payment_date']}</td>
                        <td>
                            <input type='hidden' name='payment_id' value='{$row['payment_id']}'>
                            <input type='submit' name='update' value='Save'>
                        </td>
                    </form>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No payments found for selected filters.</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
