<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

require 'config.php';

$coordinatorName = $_SESSION['name'] ?? 'Coordinator';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$filter_type = $_GET['type'] ?? 'all';

$sql = "SELECT p.*, s.student_name 
        FROM payments p 
        LEFT JOIN students s ON p.student_id = s.student_id 
        WHERE DATE(p.payment_date) BETWEEN '$start_date' AND '$end_date'";

if ($filter_type != 'all') {
    $sql .= " AND p.payment_type = '$filter_type'";
}

$sql .= " ORDER BY p.payment_date DESC";
$result = $conn->query($sql);

$total_income = 0;
$internal_income = 0;
$external_income = 0;
$transaction_count = 0;
$data_rows = [];

while ($row = $result->fetch_assoc()) {
    $total_income += $row['paid_amount'];
    $transaction_count++;
    if ($row['payment_type'] == 'Internal') $internal_income += $row['paid_amount'];
    else $external_income += $row['paid_amount'];
    $data_rows[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Financial Reports & Bill Generator</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
/* LIGHT THEME DEFAULT  */
:root{
    --bg:#f8fafc;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --accent:#2563eb;
    --accent-hover:#1d4ed8;
    --success:#16a34a;
    --border:#e2e8f0;
    --shadow: 0 4px 20px rgba(0,0,0,0.05);
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family: 'Inter', sans-serif;
    background:var(--bg);
    color:var(--text);
    display: flex;
    min-height: 100vh;
}

/* SIDEBAR FIX */
#sidebar {
    width: 280px;
    height: 100vh;
    background: #ffffff !important;
    border-right: 1px solid var(--border);
    position: fixed;
    left: 0;
    top: 0;
    z-index: 999;
}
#sidebar a, #sidebar span, #sidebar div { color: #1e293b !important; }

/* LAYOUT */
.main{
    margin-left:280px;
    padding:40px;
    width: calc(100% - 280px);
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.title{
    font-size:28px;
    font-weight:800;
}

/* TIME BOX */
.time-box{
    background:var(--card);
    padding:12px 20px;
    border-radius:14px;
    border:1px solid var(--border);
    text-align:right;
    box-shadow: var(--shadow);
}

.time{
    font-size:22px;
    font-weight:800;
    color:var(--accent);
}

.date{
    font-size:12px;
    color:var(--muted);
    margin-top:3px;
}

/* FILTER */
.filter{
    display:flex;
    gap:15px;
    align-items: center;
    background:var(--card);
    padding:18px 22px;
    border-radius:16px;
    border:1px solid var(--border);
    margin-bottom:25px;
    box-shadow: var(--shadow);
}

.input{
    padding:11px 14px;
    border-radius:10px;
    border:1px solid var(--border);
    background:#f8fafc;
    color:var(--text);
    font-family: inherit;
    font-size: 14px;
    outline: none;
    min-width:170px;
}
.input:focus { border-color: var(--accent); background: #fff; }

/* BUTTONS */
.btn{
    padding:11px 20px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.2s;
}

.primary{background:var(--accent);color:white;}
.primary:hover{background:var(--accent-hover);}

/* STATS */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:18px;
    margin-bottom:25px;
}

.card{
    background:var(--card);
    padding:22px;
    border-radius:16px;
    border:1px solid var(--border);
    box-shadow: var(--shadow);
}

.card h3{
    font-size:12px;
    color:var(--muted);
    text-transform: uppercase;
    font-weight: 700;
}

.card h1{
    font-size:24px;
    margin-top:8px;
    font-weight: 800;
}

/* TABLE */
.table{
    background:var(--card);
    border-radius:16px;
    overflow:hidden;
    border:1px solid var(--border);
    box-shadow: var(--shadow);
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
}

th{
    background: #f8fafc;
    text-align:left;
    font-size:12px;
    color:var(--muted);
    text-transform:uppercase;
    font-weight: 800;
}

td{ font-size:14px; }
tr:hover { background: #f8fafc; }

.invoice-num { font-family: monospace; font-weight: 700; color: var(--accent); }

/* BILL GENERATOR ACTION BUTTON */
.btn-bill {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: 0.2s;
}
.btn-bill:hover { background: #dcfce7; }

/*  PRINT STYLES   */
#printable-receipt {
    display: none;
}

@media print {
    body * { display: none; } /*  */
    #printable-receipt, #printable-receipt * { display: block; } /* bill */
    #printable-receipt {
        display: block !important;
        position: absolute;
        left: 0; top: 0; width: 100%;
        padding: 30px;
        color: #000;
        background: #fff;
        font-family: 'Inter', sans-serif;
    }
    .receipt-header { text-align: center; margin-bottom: 25px; border-bottom: 2px dashed #000; padding-bottom: 15px; }
    .receipt-header h2 { font-size: 24px; font-weight: 800; text-transform: uppercase; }
    .receipt-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; }
    .receipt-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .receipt-table th { background: #f0f0f0 !important; color: #000; padding: 10px; border: 1px solid #000; }
    .receipt-table td { padding: 12px 10px; border: 1px solid #000; font-size: 14px; }
    .receipt-total { text-align: right; margin-top: 20px; font-size: 18px; font-weight: 800; border-top: 2px dashed #000; padding-top: 10px; }
    .receipt-footer { text-align: center; margin-top: 40px; font-size: 12px; border-top: 1px solid #ccc; padding-top: 15px; }
}

@media(max-width:768px){
    .main{ margin-left:0; padding:18px; width: 100%; }
    .filter{ flex-direction:column; align-items: stretch; }
}
</style>
</head>

<body>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main">

<!-- HEADER -->
<div class="header">
    <div class="title">Financial Reports</div>

    <!-- LIVE TIME + DATE -->
    <div class="time-box">
        <div class="time" id="time">00:00:00</div>
        <div class="date" id="date">Loading...</div>
    </div>
</div>

<!-- FILTER -->
<form class="filter" method="GET" action="">
    <input class="input" type="date" name="start_date" value="<?= $start_date ?>">
    <input class="input" type="date" name="end_date" value="<?= $end_date ?>">

    <select class="input" name="type">
        <option value="all" <?= $filter_type == 'all' ? 'selected' : '' ?>>All Types</option>
        <option value="Internal" <?= $filter_type == 'Internal' ? 'selected' : '' ?>>Internal</option>
        <option value="External" <?= $filter_type == 'External' ? 'selected' : '' ?>>External</option>
    </select>

    <button class="btn primary"><i data-lucide="filter" style="width:16px;"></i> Filter</button>
</form>

<!-- STATS -->
<div class="stats">
    <div class="card">
        <h3>Total Income</h3>
        <h1 style="color:var(--success);">Rs. <?= number_format($total_income,2) ?></h1>
    </div>
    <div class="card">
        <h3>Transactions</h3>
        <h1><?= $transaction_count ?></h1>
    </div>
    <div class="card">
        <h3>Internal Income</h3>
        <h1>Rs. <?= number_format($internal_income,2) ?></h1>
    </div>
    <div class="card">
        <h3>External Income</h3>
        <h1>Rs. <?= number_format($external_income,2) ?></h1>
    </div>
</div>

<!-- TABLE -->
<div class="table">
<table>
<thead>
<tr>
<th>Date</th>
<th>Invoice ID</th>
<th>Student Name</th>
<th>Type</th>
<th style="text-align:right;">Amount</th>
<th style="text-align:center;">Action</th>
</tr>
</thead>

<tbody>
<?php if(!empty($data_rows)): ?>
    <?php foreach($data_rows as $row): 
        $display_name = $row['student_name'] ?? $row['customer_name'] ?? 'N/A';
        $invoice_id = str_pad($row['payment_id'], 5, '0', STR_PAD_LEFT);
        $formatted_date = date('Y-m-d (h:i A)', strtotime($row['payment_date']));
    ?>
    <tr>
        <td><?= date('Y-m-d', strtotime($row['payment_date'])) ?></td>
        <td class="invoice-num">#<?= $invoice_id ?></td>
        <td style="font-weight: 600;"><?= htmlspecialchars($display_name) ?></td>
        <td><?= $row['payment_type'] ?></td>
        <td style="text-align:right; font-weight:700; color: var(--success);">
            Rs. <?= number_format($row['paid_amount'],2) ?>
        </td>
        <td style="text-align:center;">
            
            <button type="button" class="btn-bill" 
                    onclick="generateBill('<?= $invoice_id ?>', '<?= htmlspecialchars($display_name) ?>', '<?= $row['payment_type'] ?>', '<?= number_format($row['paid_amount'],2) ?>', '<?= $formatted_date ?>')">
                <i data-lucide="receipt" style="width:14px;"></i> Print Bill
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--muted);">තෝරාගත් කාලසීමාව තුළ කිසිදු ගෙවීමක් සිදු කර නොමැත.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

</div>

<!-- HIDDEN PRINTABLE RECEIPT TEMPLATE  -->
<div id="printable-receipt">
    <div class="receipt-header">
        <h2>GLOBAL INSTITUTE</h2>
        <p style="font-size:13px; margin-top:3px;">Higher Education Center, Sri Lanka</p>
        <p style="font-size:12px; margin-top:2px;">Tel: 0112-XXXXXX / Email: info@globalinstitute.com</p>
    </div>
    
    <div class="receipt-info">
        <div>
            <p><strong>Invoice ID:</strong> <span id="bill-invoice"></span></p>
            <p><strong>Student Name:</strong> <span id="bill-name"></span></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Date/Time:</strong> <span id="bill-date"></span></p>
            <p><strong>Issued By:</strong> <?= htmlspecialchars($coordinatorName) ?></p>
        </div>
    </div>
    
    <table class="receipt-table">
        <thead>
            <tr>
                <th style="text-align:left;">Description / Course Type</th>
                <th style="text-align:right; width:150px;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Course Fee Payment (<span id="bill-type"></span>)</td>
                <td style="text-align:right; font-weight:700;">Rs. <span id="bill-amount"></span></td>
            </tr>
        </tbody>
    </table>
    
    <div class="receipt-total">
        Net Paid Amount: Rs. <span id="bill-total-final"></span>
    </div>
    
    <div class="receipt-footer">
        <p>Thank you for your payment!</p>
        <p style="margin-top:5px; font-size:10px; color:#555;">This is a computer-generated receipt.</p>
    </div>
</div>

<script>
lucide.createIcons();

/* LIVE CLOCK */
function updateTime(){
    const now = new Date();
    document.getElementById("time").innerText = now.toLocaleTimeString();
    document.getElementById("date").innerText = now.toDateString();
}
setInterval(updateTime,1000);
updateTime();

/* 📄 DYNAMIC BILL GENERATOR & PRINT LOGIC */
function generateBill(invoice, name, type, amount, date) {
    // Hidden bill
    document.getElementById('bill-invoice').innerText = "#" + invoice;
    document.getElementById('bill-name').innerText = name;
    document.getElementById('bill-type').innerText = type;
    document.getElementById('bill-date').innerText = date;
    document.getElementById('bill-amount').innerText = amount;
    document.getElementById('bill-total-final').innerText = amount;
    
    
    window.print();
}
</script>

</body>
</html>