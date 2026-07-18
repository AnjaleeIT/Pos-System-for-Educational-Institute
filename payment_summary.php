<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config.php';

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

// DELETE LOGIC... (meka kalin wagema)
if (isset($_POST['delete_payment'])) {
    $pay_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM class_payments WHERE id = ?");
    $stmt->bind_param("i", $pay_id);
    $stmt->execute();
}

$filter = isset($_GET['type']) ? $_GET['type'] : 'all';
// SQL Logic... (mekath kalin wagema)
if ($filter == 'day') { $sql = "SELECT * FROM class_payments WHERE DATE(payment_date) = '".date('Y-m-d')."' ORDER BY payment_date DESC"; }
elseif ($filter == 'english') { $sql = "SELECT * FROM class_payments WHERE LOWER(payment_category) LIKE '%english%' OR LOWER(class_name) LIKE '%english%' ORDER BY payment_date DESC"; }
elseif ($filter == 'it') { $sql = "SELECT * FROM class_payments WHERE LOWER(payment_category) LIKE '%it%' OR LOWER(class_name) LIKE '%it%' ORDER BY payment_date DESC"; }
else { $sql = "SELECT * FROM class_payments ORDER BY payment_date DESC LIMIT 50"; }
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Summary</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Dashboard Theme & Layout Width/Height */
        :root{ --bg:#eef4ff; --card:#ffffff; --text:#0f172a; --muted:#64748b; --primary:#2563eb; --shadow: 0 10px 30px rgba(37,99,235,.08); }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Inter',sans-serif; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #f8fbff 100%); color:var(--text); display:flex; min-height:100vh; }
        
        /* Sidebar layout - dashboard eke widiyatama */
        .main-content{ 
            margin-left:300px; /* Sidebar width */
            width:calc(100% - 300px); 
            padding:30px;
            min-height: 100vh;
        }

        .header-area{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-title{ font-size:34px; font-weight:900; letter-spacing:-1px; }
        
        /* Date Time Box */
        .date-time-box{ background:rgba(255,255,255,.7); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.6); padding:16px 24px; border-radius:18px; box-shadow:var(--shadow); min-width:230px; text-align:right; }
        .time{ font-size:28px; font-weight:800; color:var(--primary); }
        .date{ font-size:14px; margin-top:4px; color:var(--muted); font-weight:600; }

        /* Navigation */
        .summary-nav{ display:flex; gap:12px; margin-bottom:24px; }
        .nav-btn{ padding:12px 24px; background:#fff; border-radius:18px; text-decoration:none; color:var(--text); font-weight:700; border:1px solid #e5edf7; transition:.3s; }
        .nav-btn:hover, .nav-btn.active{ background:var(--primary); color:#fff; border-color:var(--primary); }

        /* Panel */
        .panel{ background:rgba(255,255,255,.78); backdrop-filter:blur(14px); border-radius:24px; padding:24px; border:1px solid rgba(255,255,255,.7); box-shadow:var(--shadow); }
        table{ width:100%; border-collapse:collapse; }
        th{ text-align:left; padding:18px; font-size:12px; text-transform:uppercase; color:var(--muted); }
        td{ padding:20px 18px; border-top:1px solid #eef2f7; font-weight:500; }
        
        .btn-del{ background:#fee2e2; color:#dc2626; border:none; padding:10px 20px; border-radius:12px; cursor:pointer; font-weight:600; }
        .btn-del:hover{ background:#dc2626; color:#fff; }
        .badge-class{ background:#dbeafe; color:#1e40af; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; }
    </style>
</head>
<body>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main-content">
    <header class="header-area">
        <h1 class="page-title">Payment Summary</h1>
        <div class="date-time-box">
            <div class="time" id="liveTime">00:00:00</div>
            <div class="date" id="liveDate">Loading...</div>
        </div>
    </header>

    <div class="summary-nav">
        <a href="?type=day" class="nav-btn <?php echo ($filter=='day')?'active':''; ?>">Today</a>
        <a href="?type=english" class="nav-btn <?php echo ($filter=='english')?'active':''; ?>">English</a>
        <a href="?type=it" class="nav-btn <?php echo ($filter=='it')?'active':''; ?>">IT</a>
        <a href="?type=all" class="nav-btn <?php echo ($filter=='all')?'active':''; ?>">All Records</a>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr><th>Date</th><th>Invoice</th><th>Student Details</th><th>Course</th><th>Amount</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                    <td style="font-family:monospace;font-weight:700"><?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td><strong><?php echo $row['student_name']; ?></strong><br><small style="color:var(--muted)"><?php echo $row['student_id']; ?></small></td>
                    <td><strong><?php echo $row['payment_category']; ?></strong><br><span class="badge-class"><?php echo $row['class_name']; ?></span></td>
                    <td style="font-weight:800;color:#2563eb">Rs. <?php echo number_format($row['paid_amount'], 2); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete?')">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_payment" class="btn-del">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();
    function updateDateTime(){
        const now = new Date();
        document.getElementById('liveTime').innerHTML = now.toLocaleTimeString('en-US',{ hour:'2-digit', minute:'2-digit', second:'2-digit' });
        document.getElementById('liveDate').innerHTML = now.toLocaleDateString('en-US',{ weekday:'long', year:'numeric', month:'long', day:'numeric' });
    }
    setInterval(updateDateTime,1000); updateDateTime();
</script>
</body>
</html>