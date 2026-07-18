<?php
session_start();
date_default_timezone_set('Asia/Colombo'); 

require 'config.php';

// AUTH CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$filter_course = $_GET['course'] ?? 'all';

// SQL Query using Prepared Statements
$sql = "SELECT * FROM class_payments WHERE DATE(payment_date) BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types = "ss";

if ($filter_course != 'all') {
    $sql .= " AND class_name = ?";
    $params[] = $filter_course;
    $types .= "s";
}

$sql .= " ORDER BY payment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$total_income = 0; $transaction_count = 0; $internal_income = 0; $external_income = 0;
$data_rows = [];

while ($row = $result->fetch_assoc()) {
    $amount = (float)$row['paid_amount'];
    $total_income += $amount;
    $transaction_count++;
    
    if ($row['payment_category'] == 'Internal') {
        $internal_income += $amount;
    } else {
        $external_income += $amount;
    }
    $data_rows[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coordinator Reports</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --bg: #e1edfd;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --border: #e2e8f0;
            --shadow: 0 4px 20px rgba(0,0,0,0.05);
            --radius: 20px;
        }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        
        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        
        /* Header & Date Box */
        .header-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .page-title { font-size: 35px; font-weight: 850; }
        
        .datetime-box { background: white; border:1px solid var(--border); padding:16px 24px; border-radius:18px; box-shadow:var(--shadow); min-width:200px; text-align: right;}
        .time{ font-size:22px; font-weight:800; color:var(--primary); }
        .date{ font-size:12px; margin-top:2px; color:var(--muted); font-weight:600; }

        /* Stats Grid */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card { background: var(--card); border-radius: var(--radius); padding: 25px; border: 1px solid var(--border); box-shadow: var(--shadow); }
        .card h4 { color: var(--muted); font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .card h2 { font-size: 22px; font-weight: 800; }

        /* Filter Section */
        .filter-card { background: var(--card); border-radius: var(--radius); padding: 25px; border: 1px solid var(--border); box-shadow: var(--shadow); margin-bottom: 25px; display: flex; gap: 15px; align-items: center; }
        input, select { padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border); font-size: 14px; outline: none; }
        .btn { padding: 12px 24px; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; background: var(--primary); color: #fff; transition: 0.2s; }
        .btn:hover { background: #1d4ed8; }

        /* Table */
        .table-card { background: var(--card); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 20px; background: #f8fafc; color: var(--muted); font-size: 12px; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 20px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; background: #e0e7ff; color: var(--primary); }
    </style>
</head>
<body>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main-content">
    <div class="header-section">
        <div class="page-title">Financial Reports</div>
        <div class="datetime-box">
            <div class="time" id="liveTime">00:00:00</div>
            <div class="date" id="liveDate">Loading...</div>
        </div>
    </div>

    <form class="filter-card" method="GET">
        <input type="date" name="start_date" value="<?= $start_date ?>">
        <input type="date" name="end_date" value="<?= $end_date ?>">
        <select name="course">
            <option value="all" <?= $filter_course=='all'?'selected':'' ?>>All Courses</option>
            <option value="English Course" <?= $filter_course=='English Course'?'selected':'' ?>>English Course</option>
            <option value="IT Course" <?= $filter_course=='IT Course'?'selected':'' ?>>IT Course</option>
        </select>
        <button class="btn" type="submit">Filter Report</button>
    </form>

    <div class="stats">
        <div class="card"><h4>Total Income</h4><h2>Rs. <?= number_format($total_income,2) ?></h2></div>
        <div class="card"><h4>Transactions</h4><h2><?= $transaction_count ?></h2></div>
        <div class="card"><h4>Internal</h4><h2>Rs. <?= number_format($internal_income,2) ?></h2></div>
        <div class="card"><h4>External</h4><h2>Rs. <?= number_format($external_income,2) ?></h2></div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr><th>Date</th><th>Student Name</th><th>Class Name</th><th>Category</th><th>Amount</th></tr>
            </thead>
            <tbody>
                <?php if(empty($data_rows)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 40px; color: var(--muted);">No records found matching your selection.</td></tr>
                <?php else: foreach($data_rows as $row): ?>
                <tr>
                    <td><?= $row['payment_date'] ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name'] ?? '-') ?></td>
                    <td><span class="badge"><?= htmlspecialchars($row['payment_category']) ?></span></td>
                    <td style="font-weight: 700;">Rs. <?= number_format($row['paid_amount'], 2) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Live Time Function
    function updateDateTime(){
        const now = new Date();
        document.getElementById('liveTime').innerHTML = now.toLocaleTimeString('en-US',{ hour:'2-digit', minute:'2-digit', second:'2-digit' });
        document.getElementById('liveDate').innerHTML = now.toLocaleDateString('en-US',{ weekday:'long', year:'numeric', month:'long', day:'numeric' });
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

</body>
</html>