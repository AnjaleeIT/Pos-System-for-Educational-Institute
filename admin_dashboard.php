<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

require 'config.php';

// Data Fetching
$totalIncome = $conn->query("SELECT SUM(paid_amount) AS total_income FROM class_payments")->fetch_assoc()['total_income'] ?? 0;
$countVal = $conn->query("SELECT COUNT(*) AS c FROM class_payments")->fetch_assoc()['c'] ?? 0;

// Chart Data (Category Distribution)
$catQuery = $conn->query("SELECT payment_category, COUNT(*) as count FROM class_payments GROUP BY payment_category");
$categories = []; $counts = [];
while($row = $catQuery->fetch_assoc()){ $categories[] = $row['payment_category']; $counts[] = $row['count']; }

// Chart Data (Daily Income - 7 Days)
$dailyQuery = $conn->query("SELECT DATE(payment_date) as date, SUM(paid_amount) as total FROM class_payments GROUP BY DATE(payment_date) ORDER BY DATE(payment_date) DESC LIMIT 7");
$dates = []; $dailyTotals = [];
while($d = $dailyQuery->fetch_assoc()){ $dates[] = date('M d', strtotime($d['date'])); $dailyTotals[] = $d['total']; }
$dates = array_reverse($dates); $dailyTotals = array_reverse($dailyTotals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { 
            --bg: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #f8fbff 100%); 
            --card: rgba(255,255,255,.78); 
            --text: #0f172a; 
            --primary: #2563eb; 
            --muted: #64748b; 
            --shadow: 0 10px 30px rgba(37,99,235,.08); 
            --radius: 24px; 
        }
        *{ margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body{ background: var(--bg); color:var(--text); display:flex; min-height:100vh; }
        .main-content{ margin-left:300px; width:calc(100% - 300px); padding:30px; }
        
        /* Header Area */
        .header-area{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-title{ font-size:34px; font-weight:900; color:#0f172a; letter-spacing:-1px; }
        
        /* Glassmorphism Date/Time Box */
        .datetime-box{ 
            background:rgba(255,255,255,.7); 
            backdrop-filter:blur(12px); 
            border:1px solid rgba(255,255,255,.6); 
            padding:16px 24px; 
            border-radius:18px; 
            box-shadow:var(--shadow); 
            text-align: right;
        }

        /* Glassmorphism Stats Cards */
        .stats-grid{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .card { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            border:1px solid rgba(255,255,255,.7); 
            padding: 30px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
        }
        .stat-label{ font-size:14px; color:var(--muted); font-weight:600; margin-bottom:10px; }
        .stat-value{ font-size:28px; font-weight:800; color:var(--text); }

        /* Charts */
        .charts-row{ display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .chart-box { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            padding: 30px; 
            border-radius: var(--radius); 
            border:1px solid rgba(255,255,255,.7); 
            height: 350px; 
        }
        
        /* Report Section */
        .report-section { 
            margin-top: 30px; 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            padding: 25px; 
            border-radius: var(--radius); 
            border:1px solid rgba(255,255,255,.7); 
            display: flex; 
            gap: 20px; 
            align-items: center; 
        }
        .btn-report{ padding:12px 24px; border-radius:12px; border:none; background:var(--primary); color:#fff; font-weight:700; cursor:pointer; }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header-area">
        <div class="header-left">
            <div style="color:var(--muted); font-weight:600; margin-bottom:5px;">Welcome Back, Admin</div>
            <h1 class="page-title">Admin Dashboard</h1>
        </div>
        <div class="datetime-box" id="datetime">Loading...</div>
    </div>

    <div class="stats-grid">
        <div class="card"><div class="stat-label">Total Income</div><div class="stat-value">Rs. <?php echo number_format($totalIncome, 0); ?></div></div>
        <div class="card"><div class="stat-label">Total Records</div><div class="stat-value"><?php echo $countVal; ?></div></div>
        <div class="card"><div class="stat-label">System Status</div><div class="stat-value" style="color:#05cd99; font-size: 20px;">Online</div></div>
        <div class="card"><div class="stat-label">Database</div><div class="stat-value" style="color:#2563eb; font-size: 20px;">Connected</div></div>
    </div>

    <div class="charts-row">
        <div class="chart-box">
            <h3 style="margin-bottom:20px;">Income Analytics</h3>
            <canvas id="dailyChart"></canvas>
        </div>
        <div class="chart-box">
            <h3 style="margin-bottom:20px;">Category Split</h3>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <div class="report-section">
        <strong>Quick Reports:</strong>
        <form action="daily_report.php" method="GET" style="display:flex; gap:10px;">
            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
            <button type="submit" class="btn-report">Daily Report</button>
        </form>
        <form action="monthly_report.php" method="GET" style="display:flex; gap:10px;">
            <input type="month" name="month" required value="<?php echo date('Y-m'); ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
            <button type="submit" class="btn-report">Monthly Report</button>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();
    
    // Live Clock
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
        document.getElementById('datetime').innerHTML = 
            '<div style="font-size:20px; font-weight:800; color:var(--primary)">' + now.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit', second:'2-digit'}) + '</div>' + 
            '<div style="font-size:12px; color:var(--muted); font-weight:600;">' + now.toLocaleDateString('en-GB', options) + '</div>';
    }
    setInterval(updateClock, 1000); updateClock();

    // Daily Income Chart
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{ label: 'Income (Rs.)', data: <?php echo json_encode($dailyTotals); ?>, backgroundColor: '#2563eb', borderRadius: 10, barThickness: 30 }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });

    // Category Doughnut Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($categories); ?>,
            datasets: [{ data: <?php echo json_encode($counts); ?>, backgroundColor: ['#2563eb', '#60a5fa'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%' }
    });
</script>
</body>
</html>