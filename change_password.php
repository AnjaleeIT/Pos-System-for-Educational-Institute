<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require 'config.php'; 

$message = "";
$msg_type = ""; 

if (isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id']; 

    if ($new_pass !== $confirm_pass) {
        $message = "New passwords do not match.";
        $msg_type = "error";
    } else {
        // Current password check
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($current_pass == $user['password']) {
                 $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                 $updateStmt->bind_param("si", $new_pass, $user_id);
                 
                 if ($updateStmt->execute()) {
                     $message = "Password updated successfully.";
                     $msg_type = "success";
                 } else {
                     $message = "Database error. Could not update.";
                     $msg_type = "error";
                 }
            } else {
                $message = "Current password is incorrect.";
                $msg_type = "error";
            }
        } else {
            $message = "User not found.";
            $msg_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { 
            --bg: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #f8fbff 100%); 
            --card: rgba(255,255,255,.78); 
            --primary: #2563eb; 
            --text: #0f172a; 
            --muted: #64748b;
            --shadow: 0 10px 30px rgba(37,99,235,.08);
            --radius: 24px;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }
        
        .main-content { margin-left: 300px; width: calc(100% - 300px); padding: 40px; }
        .page-title { font-size: 34px; font-weight: 900; letter-spacing: -1px; margin-bottom: 30px; }

        /* Modern Glass Card */
        .form-card { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            border: 1px solid rgba(255,255,255,.7); 
            padding: 40px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
            max-width: 500px;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text); }
        .form-control { width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 12px; background: rgba(255,255,255,.5); }
        
        .btn-submit { 
            width: 100%; padding: 14px; background: var(--primary); color: white; 
            border: none; border-radius: 12px; cursor: pointer; font-weight: 700; transition: 0.3s; 
        }
        .btn-submit:hover { background: #1d4ed8; }

        /* Alert Styling */
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        
        @media(max-width:768px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="page-title">Change Password</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" name="update_password" class="btn-submit">Update Password</button>
        </form>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>