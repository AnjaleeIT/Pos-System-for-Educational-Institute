<?php
session_start();
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);

    if ($username == "" || $password == "" || $role == "") {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND role=? AND status='active'");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($password == $user['password']) {
              
               
                $_SESSION['user_id']  = $user['user_id']; 
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // System Status Check
                if ($user['role'] == 'coordinator') {
                    $sysQuery = $conn->query("SELECT system_status FROM system_control WHERE id = 1");
                    $sysRow = $sysQuery->fetch_assoc();
                    
                    if ($sysRow && $sysRow['system_status'] == 'blocked') {
                        $error = "System is currently blocked for maintenance. Please try later.";
                    } else {
                        header("Location: coordinator_dashboard.php");
                        exit;
                    }
                } else {
                    header("Location: admin_dashboard.php");
                    exit;
                }
            } else {
                $error = "Wrong password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body{ margin:0; font-family:'Poppins',sans-serif; background:#f5f7fb; height:100vh; display:flex; justify-content:center; align-items:center; }
        .container{ width:1000px; height:550px; background:white; border-radius:20px; display:flex; overflow:hidden; box-shadow:0 20px 60px rgba(10, 21, 124, 0.1); }
        .left{ width:50%; padding:40px 50px; display:flex; flex-direction:column; justify-content:center; }
        .brand{ display:flex; align-items:center; gap:15px; margin-bottom:25px; }
        .brand img{ width:90px; }
        .brand-text{ font-family:'Montserrat',sans-serif; font-weight:900; line-height:1.2; }
        .brand-text span{ display:block; font-size:19px; font-weight:400; color:blue; }
        .left h2{ margin-bottom:20px; }
        input, select{ width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #d4c6fc; }
        button{ width:100%; padding:12px; background:linear-gradient(135deg,#7c3aed,#6366f1); color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; }
        button:hover{ opacity:0.9; }
        .error{ background:#fee2e2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:10px; }
        .right{ width:50%; background:#fafafa; position:relative; display:flex; justify-content:center; align-items:center; text-align:center; padding:20px; }
        .shape{ position:absolute; border-radius:50%; opacity:0.6; }
        .shape1{ width:200px; height:200px; background:linear-gradient(135deg,#a78bfa,#6366f1); top:-50px; left:50%; }
        .shape2{ width:120px; height:120px; background:linear-gradient(135deg,#fb7185,#f97316); bottom:40px; left:40px; }
        .shape3{ width:150px; height:150px; background:linear-gradient(135deg,#22d3ee,#3b82f6); bottom:-50px; right:50px; }
        @media(max-width:768px){ .container{ flex-direction:column; width:95%; height:auto; } .left,.right{ width:100%; } }
    </style>
</head>
<body>
<div class="container">
    <div class="left">
        <div class="brand">
            <img src="logo1111111.png" alt="logo">
            <div class="brand-text">Global Institute Of <span>English & IT</span></div>
        </div>
        <h2>Login to your account</h2>
        <?php if($error!="") echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role">
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="coordinator">Coordinator</option>
            </select>
            <button type="submit">Login</button>
        </form>
    </div>
    <div class="right">
        <h1>“Manage payments with ease and accuracy.”</h1>
        <div class="shape shape1"></div><div class="shape shape2"></div><div class="shape shape3"></div>
    </div>
</div>
</body>
</html>