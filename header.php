<?php
// includes/header.php
if (!isset($no_header)) {
    // ok
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>POS System</title>
<style>
/* Internal CSS for simple clean UI */
body { font-family: Arial, sans-serif; margin:0; padding:0; background:#f5f7fa; color:#222; }
.container { max-width:1000px; margin:30px auto; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:20px; box-shadow:0 6px 18px rgba(0,0,0,0.03); }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.nav { display:flex; gap:10px; }
.btn { display:inline-block; padding:8px 12px; background:#2b6cb0; color:#fff; text-decoration:none; border-radius:6px; }
.btn.secondary { background:#6b7280; }
.card { background:#ffffff; border:1px solid #eef2f71d; padding:15px; border-radius:8px; margin-bottom:16px; }
.table { width:100%; border-collapse:collapse; }
.table th, .table td { padding:8px 10px; border-bottom:1px solid #eef2f72e; text-align:left; }
.form-row { display:flex; gap:10px; margin-bottom:10px; align-items:center; }
.form-row input[type="text"], .form-row input[type="number"], .form-row select { padding:8px; border:1px solid #cbd5e1; border-radius:6px; width:100%; }
label { font-weight:600; width:150px; display:inline-block; }
.small { font-size:0.9rem; color:#4b5563; }
.error { color:#b91c1c; }
.success { color:#047857; }
.footer { text-align:center; color:#6b7280; margin-top:20px; font-size:0.9rem; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h2>POS - Educational Institute</h2>
      <div class="small">Web POS (PHP + MySQL) — Coordinator / Admin</div>
    </div>
    <div class="nav">
<?php if(isset($_SESSION['user'])): ?>
      <a class="btn secondary" href="/pos/logout.php">Logout</a>
<?php else: ?>
      <a class="btn" href="/pos/index.php">Login</a>
<?php endif; ?>
    </div>
  </div>
