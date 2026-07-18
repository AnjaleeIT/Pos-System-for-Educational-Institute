<?php
require 'config.php';
$result = $conn->query("SELECT * FROM payments ORDER BY date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Summary</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Day Summary</h2>
<table border="1">
    <tr>
        <th>Student ID</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Date</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['student_id'] ?></td>
        <td><?= $row['type'] ?></td>
        <td><?= $row['amount'] ?></td>
        <td><?= $row['date'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
