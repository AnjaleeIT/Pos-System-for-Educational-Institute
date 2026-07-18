<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

require 'config.php';

$coordinatorName = isset($_SESSION['name']) 
    ? htmlspecialchars($_SESSION['name']) 
    : 'Coordinator';

// ---------------- FETCH DATA (Now from class_payments only) ----------------

$today = date('Y-m-d');

// 1. Today's Payments
$result_today = $conn->query("
    SELECT SUM(paid_amount) as total_today 
    FROM class_payments 
    WHERE DATE(payment_date) = '$today'
");
$total_today = $result_today->fetch_assoc()['total_today'] ?? 0;

// 2. Total Revenue
$result_revenue = $conn->query("
    SELECT SUM(paid_amount) as total_revenue 
    FROM class_payments
");
$total_revenue = $result_revenue->fetch_assoc()['total_revenue'] ?? 0;

// 3. Active Students
$result_students = $conn->query("
    SELECT COUNT(DISTINCT student_id) as total_students 
    FROM class_payments
");
$total_students = $result_students->fetch_assoc()['total_students'] ?? 0;

// Pending
$pending_approvals = 0;

// Stats
$stats = [
    [
        'label' => 'Payments Today',
        'value' => 'Rs. ' . number_format($total_today, 2),
        'icon_class' => 'wallet',
        'card_class' => 'blue-card'
    ],
    [
        'label' => 'Pending Approvals',
        'value' => $pending_approvals,
        'icon_class' => 'clock-3',
        'card_class' => 'orange-card'
    ],
    [
        'label' => 'Total Revenue',
        'value' => 'Rs. ' . number_format($total_revenue, 2),
        'icon_class' => 'bar-chart-3',
        'card_class' => 'green-card'
    ],
    [
        'label' => 'Active Students',
        'value' => $total_students,
        'icon_class' => 'users',
        'card_class' => 'purple-card'
    ]
];

// Recent Transactions
$recentPayments = [];

// Query updated to use class_payments columns (id as payment_id, student_name as customer_name)
$sql_recent = "
SELECT id AS payment_id, student_id, student_name AS customer_name, paid_amount, payment_date 
FROM class_payments 
ORDER BY payment_date DESC 
LIMIT 5
";

$result_recent = $conn->query($sql_recent);

if ($result_recent && $result_recent->num_rows > 0) {
    while ($row = $result_recent->fetch_assoc()) {
        $client_name = !empty($row['customer_name']) 
            ? $row['customer_name'] 
            : 'Student ID: ' . $row['student_id'];

        $recentPayments[] = [
            'invoice' => '#INV' . str_pad($row['payment_id'], 4, '0', STR_PAD_LEFT),
            'client' => $client_name,
            'amount' => 'Rs. ' . number_format($row['paid_amount'], 2),
            'status' => 'Completed'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Coordinator Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<style>


:root{ --bg:#eef4ff; --bg2:#dbeafe; --card:#ffffff; --text:#0f172a; --muted:#64748b; --primary:#2563eb; --primary2:#1d4ed8; --border:#dbe4f0; --shadow: 0 10px 30px rgba(37,99,235,.08); --radius:18px; }
*{ margin:0; padding:0; box-sizing:border-box; }
body{ font-family:'Inter',sans-serif; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #f8fbff 100%); color:var(--text); display:flex; min-height:100vh; }
.main-content{ margin-left:300px; width:calc(100% - 300px); padding:30px; }
.header-area{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
.header-left{ display:flex; flex-direction:column; gap:10px; }
.top-welcome{ font-size:14px; color:var(--muted); font-weight:600; }
.page-title{ font-size:34px; font-weight:900; color:#0f172a; letter-spacing:-1px; }
.date-time-box{ background:rgba(255,255,255,.7); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.6); padding:16px 24px; border-radius:18px; box-shadow:var(--shadow); min-width:230px; }
.time{ font-size:28px; font-weight:800; color:var(--primary); }
.date{ font-size:14px; margin-top:4px; color:var(--muted); font-weight:600; }
.grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:22px; margin-bottom:30px; }
.stat-card{ position:relative; background:rgba(255,255,255,.75); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,.6); border-radius:24px; padding:24px; overflow:hidden; transition:.3s; box-shadow:var(--shadow); }
.stat-card:hover{ transform:translateY(-6px); }
.stat-card::before{ content:""; position:absolute; right:-40px; top:-40px; width:120px; height:120px; border-radius:50%; opacity:.1; }
.blue-card::before{ background:#2563eb; } .orange-card::before{ background:#f59e0b; } .green-card::before{ background:#10b981; } .purple-card::before{ background:#8b5cf6; }
.card-top{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.icon-box{ width:58px; height:58px; border-radius:18px; display:flex; align-items:center; justify-content:center; }
.blue-card .icon-box{ background:rgba(37,99,235,.12); color:#2563eb; }
.orange-card .icon-box{ background:rgba(245,158,11,.14); color:#f59e0b; }
.green-card .icon-box{ background:rgba(16,185,129,.14); color:#10b981; }
.purple-card .icon-box{ background:rgba(139,92,246,.14); color:#8b5cf6; }
.label{ color:var(--muted); font-size:14px; font-weight:600; }
.value{ font-size:28px; font-weight:900; margin-top:8px; }
.dashboard-bottom{ display:grid; grid-template-columns:2fr 1fr; gap:24px; }
.panel{ background:rgba(255,255,255,.78); backdrop-filter:blur(14px); border-radius:24px; padding:24px; border:1px solid rgba(255,255,255,.7); box-shadow:var(--shadow); }
.card-header{ font-size:20px; font-weight:800; margin-bottom:22px; }
table{ width:100%; border-collapse:collapse; }
th{ text-align:left; padding:14px; font-size:12px; text-transform:uppercase; color:var(--muted); }
td{ padding:16px 14px; border-top:1px solid #eef2f7; font-size:14px; font-weight:500; }
.status-badge{ padding:7px 14px; background:rgba(16,185,129,.12); color:#10b981; border-radius:50px; font-size:12px; font-weight:700; }
.tools-grid{ display:flex; flex-direction:column; gap:16px; }
.tool-btn{ display:flex; align-items:center; gap:14px; padding:18px; border-radius:18px; text-decoration:none; color:var(--text); background:#f8fbff; border:1px solid #e5edf7; font-weight:700; transition:.3s; }
.tool-btn:hover{ transform:translateX(5px); background:#eff6ff; }
.tool-btn i{ color:var(--primary); }
@media(max-width:992px){ .dashboard-bottom{ grid-template-columns:1fr; } }
@media(max-width:768px){ .main-content{ margin-left:0; width:100%; padding:20px; } .header-area{ flex-direction:column; align-items:flex-start; gap:20px; } .grid{ grid-template-columns:1fr; } }
</style>
</head>
<body>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main-content">
    <header class="header-area">
        <div class="header-left">
            <div class="top-welcome">Welcome Back, <?php echo $coordinatorName; ?></div>
            <h1 class="page-title">Coordinator Dashboard</h1>
        </div>
        <div class="date-time-box">
            <div class="time" id="liveTime">00:00:00</div>
            <div class="date" id="liveDate">Loading...</div>
        </div>
    </header>

    <section class="grid">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-card <?php echo $stat['card_class']; ?>">
                <div class="card-top">
                    <div class="icon-box"><i data-lucide="<?php echo $stat['icon_class']; ?>"></i></div>
                </div>
                <div class="label"><?php echo $stat['label']; ?></div>
                <div class="value"><?php echo $stat['value']; ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <div class="dashboard-bottom">
        <div class="panel">
            <div class="card-header">Recent Transactions</div>
            <table>
                <thead>
                    <tr><th>Invoice</th><th>Client</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php if(empty($recentPayments)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--muted)">No recent transactions found</td></tr>
                <?php else: ?>
                    <?php foreach($recentPayments as $pay): ?>
                    <tr>
                        <td style="font-family:monospace;font-weight:700"><?php echo $pay['invoice']; ?></td>
                        <td><?php echo $pay['client']; ?></td>
                        <td style="font-weight:800;color:#2563eb"><?php echo $pay['amount']; ?></td>
                        <td><span class="status-badge"><?php echo $pay['status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="card-header">Quick Actions</div>
            <div class="tools-grid">
                <a href="calculator.php" class="tool-btn"><i data-lucide="plus-circle"></i> Calculator</a>
                <a href="payment_summary.php" class="tool-btn"><i data-lucide="history"></i> Payment History</a>
                <a href="coordinator_reports.php" class="tool-btn"><i data-lucide="file-bar-chart"></i> Reports</a>
            </div>
        </div>
    </div>
</div>

<script>
lucide.createIcons();
function updateDateTime(){
    const now = new Date();
    document.getElementById('liveTime').innerHTML = now.toLocaleTimeString('en-US',{ hour:'2-digit', minute:'2-digit', second:'2-digit' });
    document.getElementById('liveDate').innerHTML = now.toLocaleDateString('en-US',{ weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
setInterval(updateDateTime,1000);
updateDateTime();
</script>
</body>
</html>