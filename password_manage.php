<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

require 'config.php';

// Change Admin Password
$adminMsg = "";
if (isset($_POST['change_admin'])) {
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];

    if ($new !== $confirm) {
        $adminMsg = "❌ New password and confirm password do not match.";
    } else {
        // Check old password
        $check = $conn->prepare("SELECT * FROM users WHERE username='admin' AND password=?");
        $check->bind_param("s", $old);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $update = $conn->prepare("UPDATE users SET password=? WHERE username='admin'");
            $update->bind_param("s", $new);
            $update->execute();
            $adminMsg = "✅ Admin password successfully updated.";
        } else {
            $adminMsg = "❌ Old password is incorrect.";
        }
    }
}

// Reset Coordinator Password
$coordMsg = "";
if (isset($_POST['reset_coord'])) {
    $newCoordPass = $_POST['coord_new_pass'];
    $update = $conn->prepare("UPDATE users SET password=? WHERE role='coordinator'");
    $update->bind_param("s", $newCoordPass);
    $update->execute();
    $coordMsg = "✅ Coordinator password has been reset successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Management</title>
<link rel="stylesheet" href="style.css">
<style>
.container {
    max-width: 800px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
section {
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
h3 {
    color: #007bff;
    margin-top: 0;
}
label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
}
input[type=password] {
    width: 100%;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-top: 5px;
}
button {
    padding: 10px 20px;
    margin-top: 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.save-btn {
    background: #007bff;
    color: white;
}
.save-btn:hover {
    background: #0056b3;
}
.back-btn {
    background: #28a745;
    color: white;
}
.back-btn:hover {
    background: #1f7a31;
}
.message {
    margin-top: 10px;
    font-weight: bold;
}
.success {
    color: green;
}
.error {
    color: red;
}
</style>
</head>
<body>
<header>Password Management (Admin Only)</header>

<div class="container">

    <!-- Admin Password Change -->
    <section>
        <h3>Change Admin Password</h3>
        <form method="POST">
            <label>Old Password:</label>
            <input type="password" name="old_pass" required>

            <label>New Password:</label>
            <input type="password" name="new_pass" required>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_pass" required>

            <button type="submit" name="change_admin" class="save-btn">Update Password</button>
        </form>
        <?php if (!empty($adminMsg)): ?>
            <div class="message <?php echo (str_contains($adminMsg, '✅')) ? 'success' : 'error'; ?>">
                <?php echo $adminMsg; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Coordinator Password Reset -->
    <section>
        <h3>Reset Coordinator Password</h3>
        <form method="POST">
            <label>Enter New Coordinator Password:</label>
            <input type="password" name="coord_new_pass" required>
            <button type="submit" name="reset_coord" class="save-btn">Reset Password</button>
        </form>
        <?php if (!empty($coordMsg)): ?>
            <div class="message success">
                <?php echo $coordMsg; ?>
            </div>
        <?php endif; ?>
    </section>

    <button class="back-btn" onclick="window.location.href='admin_dashboard.php'">Back to Dashboard</button>
</div>

</body>
</html>
