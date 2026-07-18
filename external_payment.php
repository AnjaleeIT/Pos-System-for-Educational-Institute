<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Authentication Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

require 'config.php';

// 1. Handle Payment Submission
$msg = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['service_label'];
    $applicant_name = trim($_POST['applicant_name']) ?: null;
    $nic = trim($_POST['nic']) ?: null;
    $contact_no = trim($_POST['contact_no']) ?: null;
    $amount = floatval($_POST['amount']);
    $paid_amount = floatval($_POST['paid_amount']);
    $payment_method = $_POST['payment_method'];
    $balance = $amount - $paid_amount;
    
    // Prepare SQL
    $sql = "INSERT INTO payments (payment_type, category, customer_name, nic, contact, total_amount, paid_amount, balance, payment_method, payment_date) 
            VALUES ('External', ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddds", $category, $applicant_name, $nic, $contact_no, $amount, $paid_amount, $balance, $payment_method);
    
    if ($stmt->execute()) {
        $payment_id = $conn->insert_id;
        $msg = "Payment recorded successfully! Invoice #INV" . str_pad($payment_id, 4, '0', STR_PAD_LEFT);
        $msg_type = "success";
    } else {
        $msg = "Error: " . $conn->error;
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>External Payment - Nenasala</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
/* --- CSS VARIABLES (Standard) --- */
:root {
    --bg-dark: #0e1218ff; --bg-darker: #161e30ff; --card-bg: #3d4552ff;
    --muted: #eeececff; --accent-color: #3076e6ff;
    --accent: linear-gradient(135deg, #3076e6ff, #2563eb);
    --success: #2dbb06ff; --danger: #ef2121ff;
    --glass: rgba(255, 255, 255, 0.08); --radius: 12px; --text: #f9fafb;
    --card-border: #1a1f25ff; --input-bg: #2d3542; --shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}
.light-theme {
    --bg-dark: #f4f7f9; --bg-darker: #ffffff; --card-bg: #ffffff;
    --muted: #6b7280; --accent: linear-gradient(135deg, #3076e6ff, #2563eb);
    --success: #059669; --danger: #ef4444; --glass: rgba(0, 0, 0, 0.05);
    --text: #1f2937; --card-border: #e5e7eb; --input-bg: #f9fafb;
}

* { box-sizing: border-box; margin: 0; padding: 0; outline: none; }
body {
    font-family: 'Inter', sans-serif; background-color: var(--bg-dark); color: var(--text);
    display: flex; min-height: 100vh; transition: background 0.3s ease, color 0.3s ease;
    background-image: url('bg.png'); background-size: cover; background-attachment: fixed;
    position: relative; z-index: 9;
}
body::before { content: ""; position: fixed; inset: 0; background: rgba(4, 30, 63, 0.85); z-index: -1; }
.light-theme::before { background: rgba(244, 247, 249, 0.92); }

/* --- SIDEBAR STYLES (Essential) --- */
.sidebar {
    width: 280px; background: var(--bg-darker); border-right: 1px solid var(--card-border);
    padding: 22px; display: flex; flex-direction: column; gap: 25px;
    position: fixed; height: 100vh; z-index: 100; transition: transform 0.3s ease;
}
.brand { display:flex; gap:12px; align-items:center; padding-bottom: 20px; border-bottom: 1px solid var(--card-border); }
.brand-logo { width: 80px; height: 50px; border-radius: 2px; object-fit: cover; flex-shrink: 0; }
.brand .title .name{ font-weight:900; font-size:18px; color: var(--text); }
.brand .title .sub{ font-size:12px; color:var(--muted); margin-top:2px; font-weight:500; }

.nav { display:flex; flex-direction:column; gap:6px; margin-top:6px; }
.nav a {
    display:flex; gap:12px; align-items:center;
    padding:12px 14px; border-radius:var(--radius);
    color:var(--muted); text-decoration:none; font-weight:500;
    transition: all .2s ease;
}
.nav a:hover, .nav a.active { color:var(--text); background: var(--glass); }
.nav a.active { background: var(--accent); color: #fff; }
.mt-auto { margin-top: auto; }

.sidebar-footer { margin-top: auto; display: flex; flex-direction: column; gap: 15px; padding-top: 20px; border-top: 1px solid var(--card-border); align-items: center; }
.logout-btn {
    display:inline-flex; gap:8px; align-items:center; justify-content:center; width: 100%;
    background:linear-gradient(135deg,#ff5f6d,#dc2626) !important; color: #fff;
    padding:10px 12px; border-radius:10px; text-decoration:none;
}

/* Removed Top Right Theme Toggle CSS */
.mobile-toggle { display: none; background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text); padding: 10px; border-radius: 8px; cursor: pointer; margin-right: 15px; }
.hamburger { background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text); padding: 10px; border-radius: 8px; cursor: pointer; display: none; }

/* --- MAIN CONTENT --- */
.main-content {
    margin-left: 280px; flex: 1; padding: 30px; width: calc(100% - 280px);
    transition: margin-left 0.3s ease, width 0.3s ease;
}
.header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--card-border); padding-bottom: 15px; }
.page-title { font-size: 1.75rem; font-weight: 800; color: var(--text); }

/* --- FORM STYLES --- */
.form-container { max-width: 800px; margin: 0 auto; }
.card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 30px; box-shadow: var(--shadow); }

form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.full-width { grid-column: span 2; }

.form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--muted); }
.form-control {
    width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid var(--card-border);
    background: var(--input-bg); color: var(--text); font-size: 1rem; transition: 0.2s;
}
.form-control:focus { border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(48, 118, 230, 0.25); outline: none; }

.btn-submit {
    width: 100%; padding: 15px; font-size: 1.1rem; font-weight: 700; color: white;
    background: var(--accent); border: none; border-radius: 12px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 10px; transition: transform 0.2s;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(48, 118, 230, 0.4); }

.form-note { grid-column: span 2; text-align: center; font-size: 0.85rem; color: var(--muted); margin-top: 10px; background: var(--bg-darker); padding: 10px; border-radius: 8px; }

.alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.alert-success { background: rgba(5, 150, 104, 0.15); color: var(--success); border: 1px solid var(--success); }
.alert-error { background: rgba(239, 33, 33, 0.15); color: var(--danger); border: 1px solid var(--danger); }

/* Responsive */
@media(max-width: 768px) { 
    .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); }
    .main-content { margin-left: 0; width: 100%; padding: 15px; }
    .mobile-toggle { display: block; } .hamburger { display: block; }
    form { grid-template-columns: 1fr; } .full-width { grid-column: span 1; }
}
.overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 90; display: none; }
.overlay.active { display: block; }
</style>
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main-content">
    
    <header class="header-area">
        <div style="display:flex; align-items:center;">
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i data-lucide="menu"></i>
            </button>
            <h1 class="page-title">External Payment</h1>
        </div>
        </header>

    <div class="form-container">
        
        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?>">
                <i data-lucide="<?php echo ($msg_type == 'success') ? 'check-circle' : 'alert-circle'; ?>"></i>
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                
                <div class="full-width">
                    <label>Service Type</label>
                    <select name="service_label" class="form-control" required>
                        <option value="" disabled selected>Select Service</option>
                        <option value="UGC Registration">UGC Registration</option>
                        <option value="A/L Registration">A/L Registration</option>
                        <option value="O/L Registration">O/L Registration</option>
                        <option value="Police Report">Police Report</option>
                        <option value="Other">Other Service</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Customer Name (Optional)</label>
                    <input type="text" name="applicant_name" class="form-control" placeholder="Full Name">
                </div>

                <div class="form-group">
                    <label>NIC Number (Optional)</label>
                    <input type="text" name="nic" class="form-control" placeholder="NIC No">
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_no" class="form-control" placeholder="07x xxx xxxx">
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Total Charge (Rs)</label>
                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" required oninput="calcBalance()">
                </div>

                <div class="form-group">
                    <label>Paid Amount (Rs)</label>
                    <input type="number" name="paid_amount" id="paid_amount" class="form-control" step="0.01" required oninput="calcBalance()">
                </div>

                <div class="full-width">
                    <label>Balance / Change</label>
                    <input type="text" id="balance" class="form-control" readonly style="font-weight:bold; color:var(--success);">
                </div>

                <div class="full-width">
                    <button type="submit" class="btn-submit">
                        <i data-lucide="printer"></i> Generate Bill & Save
                    </button>
                </div>

                <div class="form-note">
                    <i data-lucide="info" width="14" style="vertical-align:middle"></i> 
                    Note: Use "Internal Payment" for registered students and course fees.
                </div>

            </form>
        </div>
    </div>

</div>

<script>
    lucide.createIcons();

    // Calculation Logic
    function calcBalance(){
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
        const balance = paid - amount; 
        document.getElementById('balance').value = 'Rs. ' + balance.toFixed(2);
        
        const balEl = document.getElementById('balance');
        if(balance < 0) balEl.style.color = 'var(--danger)';
        else balEl.style.color = 'var(--success)';
    }

    // Theme Logic (Button is now only in Sidebar)
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    if(savedTheme === 'light') {
        body.classList.add('light-theme');
    }

   
    
    // Re-adding listener just in case sidebar doesn't have the script block
    const themeBtn = document.getElementById('themeToggle'); 
    if(themeBtn) {
        themeBtn.addEventListener('click', () => {
            body.classList.toggle('light-theme');
            const isLight = body.classList.contains('light-theme');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
        });
    }

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const collapseBtn = document.getElementById('collapseBtn');

    function toggleSidebar() { 
        sidebar.classList.toggle('active'); 
        overlay.classList.toggle('active'); 
    }
    function closeSidebar() { 
        sidebar.classList.remove('active'); 
        overlay.classList.remove('active'); 
    }
    if(collapseBtn) {
        collapseBtn.addEventListener('click', () => {
             if (window.innerWidth <= 768) closeSidebar();
        });
    }
</script>

</body>
</html>