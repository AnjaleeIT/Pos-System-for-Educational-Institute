<?php
session_start();
require 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if ($username === '' || $password === '' || $role === '') {
        $error = "All fields are required.";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ? AND status='active'");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['password'] === $password) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                    exit;
                } elseif ($user['role'] === 'coordinator') {
                    header("Location: coordinator_dashboard.php");
                    exit;
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Global Institute</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:#f3f6fb;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container{
    width:900px;
    height:500px;
    background:#fff;
    border-radius:20px;
    display:flex;
    box-shadow:0 15px 40px rgba(0,0,0,0.1);
    overflow:hidden;
}

/* LEFT SIDE */
.left{
    width:50%;
    background:#f7f9fc;
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:40px;
}

.logo{
    font-size:22px;
    font-weight:700;
    margin-bottom:20px;
}

.logo span{
    color:#2563eb;
}

h2{
    margin-bottom:10px;
}

p{
    color:#666;
    margin-bottom:30px;
}

input,select{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border-radius:10px;
    border:1px solid #ddd;
    outline:none;
    font-size:14px;
}

input:focus,select:focus{
    border-color:#2563eb;
}

button{
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
}

button:hover{
    background:#1e40af;
}

.error{
    background:#fee2e2;
    color:#b91c1c;
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
}

/* RIGHT SIDE */
.right{
    width:50%;
    background:linear-gradient(135deg,#2563eb,#4f46e5);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    text-align:center;
    padding:20px;
}

.right h1{
    font-size:28px;
}

.right p{
    color:#e0e7ff;
}

@media(max-width:768px){
    .container{
        flex-direction:column;
        width:95%;
        height:auto;
    }
    .left,.right{
        width:100%;
    }
}

</style>
</head>

<body>

<div class="container">

    <!-- LEFT LOGIN FORM -->
    <div class="left">

        <div class="logo">Global <span>Institute</span></div>

        <h2>Login to your account</h2>
        <p>Enter your credentials to continue</p>

        <?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">

            <input type="text" name="username" placeholder="Username" required>

            <input type="password" name="password" placeholder="Password" required>

            <select name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="coordinator">Coordinator</option>
            </select>

            <button type="submit">Login</button>

        </form>

    </div>

    <!-- RIGHT SIDE DESIGN -->
    <div class="right">
        <div>
            <h1>Welcome Back 👋</h1>
            <p>Global Institute of English & IT Payment System</p>
        </div>
    </div>

</div>

</body>
</html>