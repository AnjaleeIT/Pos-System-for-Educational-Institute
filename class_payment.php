<?php
session_start();
date_default_timezone_set('Asia/Colombo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ================= AUTH CHECK =================
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

require 'config.php';

// ================= FETCH FEES FROM DATABASE =================
$fees_data = [];
$result = $conn->query("SELECT * FROM fees");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $type_key = strtolower(trim($row['type'])); 
        $name_key = trim($row['name']);             
        $fees_data[$type_key][$name_key] = $row['total_fee'];
    }
}

// ================= HANDLE PAYMENT SUBMISSION & BILL GENERATION =================
$msg = "";
$msg_type = "";
$show_receipt = false; 
$receipt_data = [];

if (isset($_POST['submit_payment'])) {
    $student_id       = trim($_POST['student_id']);
    $student_name     = trim($_POST['student_name']); 
    $class_type       = $_POST['category'];        
    $class_level      = $_POST['class_level'];     
    $total_fee        = (float)$_POST['total_amount'];
    $paid             = (float)$_POST['paid_amount'];
    $balance          = $paid - $total_fee;
    $method           = $_POST['payment_method'];
    
    $class_name       = strtoupper($class_type) . " - " . $class_level; 
    $payment_category = "Class Payment"; 

    $check_stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows == 0) {
        $insert_student = $conn->prepare("INSERT INTO students (student_id, name, class_name, registered_date) VALUES (?, ?, ?, NOW())");
        $insert_student->bind_param("sss", $student_id, $student_name, $class_name);
        $insert_student->execute();
    }

    $stmt = $conn->prepare("INSERT INTO class_payments (student_id, student_name, class_type, class_level, class_name, total_amount, paid_amount, balance, payment_method, payment_category, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssdddss", $student_id, $student_name, $class_type, $class_level, $class_name, $total_fee, $paid, $balance, $method, $payment_category);

    if ($stmt->execute()) {
        $msg = "Class Payment Recorded Successfully!";
        $msg_type = "success";
        
        if (isset($_POST['generate_bill_opt'])) {
            $show_receipt = true;
            $receipt_data = [
                'id' => $conn->insert_id, 
                'name' => $student_name, 
                'class' => $class_name, 
                'total' => $total_fee,
                'amount' => $paid, 
                'balance' => $balance,
                'method' => $method,
                'date' => date('Y-m-d'),
                'time' => date('H:i:s')
            ];
        }
    } else {
        $msg = "Database Error : " . $conn->error;
        $msg_type = "error";
    }
}

$class_levels = ["Grade 6", "Grade 7", "Grade 8", "Grade 9", "Ordinary Level", "Advanced Level"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Payment & Billing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{ --primary:#2563eb; --primary-dark:#1d4ed8; --bg:#eff6ff; --card:rgba(255,255,255,0.85); --text:#0f172a; --muted:#64748b; --border:#dbeafe; --success:#16a34a; --danger:#dc2626; --warning:#d97706; --shadow: 0 20px 40px rgba(37,99,235,0.08); --radius:24px; }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Inter',sans-serif; background: linear-gradient(135deg, #dbeafe, #eff6ff, #ffffff); min-height:100vh; color:var(--text); }
        .main-content{ margin-left:310px; padding:35px; }
        .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:35px; }
        .page-title{ font-size:38px; font-weight:800; color:var(--text); letter-spacing:-1px; }
        .page-sub{ margin-top:6px; color:var(--muted); font-size:15px; }
        .datetime-box{ background:rgba(255,255,255,0.75); backdrop-filter:blur(15px); padding:18px 24px; border-radius:20px; border:1px solid rgba(255,255,255,0.5); box-shadow:var(--shadow); text-align:right; }
        .datetime-box .time{ font-size:30px; font-weight:800; color:var(--primary); }
        .datetime-box .date{ margin-top:4px; font-size:13px; color:var(--muted); }
        .payment-card{ background:var(--card); backdrop-filter:blur(18px); border-radius:32px; padding:35px; border:1px solid rgba(255,255,255,0.5); box-shadow:var(--shadow); }
        .card-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .card-header h2{ font-size:28px; font-weight:800; }
        .badge{ background:#dbeafe; color:var(--primary); padding:10px 18px; border-radius:14px; font-size:13px; font-weight:700; }
        .alert{ padding:16px 18px; border-radius:16px; margin-bottom:25px; font-weight:600; }
        .success{ background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .error{ background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:24px; }
        .form-group{ display:flex; flex-direction:column; }
        label{ margin-bottom:8px; font-size:14px; font-weight:700; color:#334155; }
        input, select{ width:100%; padding:16px; border-radius:16px; border:1px solid var(--border); background:white; font-size:15px; transition:0.25s; outline: none; }
        input:focus, select:focus{ border-color:var(--primary); box-shadow: 0 0 0 5px rgba(37,99,235,0.10); }
        input[readonly]{ background-color: #f1f5f9; color: #475569; cursor: not-allowed; }
        
        /* Checkbox Option Style */
        .option-group { display: flex; align-items: center; gap: 10px; margin-top: 15px; background: rgba(255,255,255,0.6); padding: 12px 16px; border-radius: 14px; border: 1px solid var(--border); width: fit-content; }
        .option-group input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }
        .option-group label { margin-bottom: 0; cursor: pointer; color: var(--text); font-size: 15px; }

        .summary-box{ margin-top:30px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius:24px; padding:28px; border:1px solid #bfdbfe; }
        .summary-title{ font-size:22px; font-weight:800; margin-bottom:25px; }
        .summary-row{ display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
        .summary-label{ font-size:16px; font-weight:700; color:#334155; }
        .summary-value{ font-size:30px; font-weight:800; color:var(--primary); }
        .submit-btn{ width:100%; margin-top:30px; padding:18px; border:none; border-radius:18px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color:white; font-size:16px; font-weight:800; cursor:pointer; transition:0.3s; box-shadow: 0 15px 30px rgba(37,99,235,0.25); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .submit-btn:hover{ transform:translateY(-2px); }
        @media(max-width:900px){ .main-content{ margin-left:0; padding:20px; } .form-grid{ grid-template-columns:1fr; } .topbar{ flex-direction:column; align-items:flex-start; gap:20px; } }

        /* ================= PRINT STYLE FOR RECEIPT ================= */
        #receipt-print-area { display: none; }
        @media print {
            body * { display: none; }
            #receipt-print-area, #receipt-print-area * { display: block !important; }
            #receipt-print-area {
                width: 80mm;
                margin: 0 auto;
                padding: 10px;
                font-family: 'Courier New', Courier, monospace;
                color: #000;
            }
            .receipt-header { text-align: center; margin-bottom: 15px; }
            .receipt-header h2 { font-size: 18px; margin: 0; font-weight: 800; }
            .receipt-info { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin: 10px 0; font-size: 13px; }
            .receipt-row { display: flex; justify-content: space-between; margin: 5px 0; font-size: 13px; }
            .receipt-total { font-size: 15px; font-weight: bold; margin-top: 10px; border-top: 1px dashed #000; padding-top: 8px; }
            .receipt-footer { text-align: center; margin-top: 25px; font-size: 12px; }
        }
    </style>
</head>
<body>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div>
            <div class="page-title">Class Payment & Billing</div>
            <div class="page-sub">Manage student payments and instantly generate invoice receipts.</div>
        </div>
        <div class="datetime-box">
            <div class="time" id="liveTime">00:00:00</div>
            <div class="date" id="liveDate">Loading...</div>
        </div>
    </div>

    <div class="payment-card">
        <div class="card-header">
            <h2>Student Class Payment Form</h2>
            <div class="badge">Coordinator Access</div>
        </div>

        <?php if($msg): ?>
            <div class="alert <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" id="studentIdInput" placeholder="Enter Student ID" oninput="fetchStudentName()" required>
                </div>
                <div class="form-group">
                    <label>Student Name</label>
                    <input type="text" name="student_name" id="studentNameInput" placeholder="Enter Student Name" required>
                </div>
                <div class="form-group">
                    <label>Class Type</label>
                    <select name="category" id="categorySelect" onchange="updateFee()" required>
                        <option value="">Select Type</option>
                        <option value="english">English</option>
                        <option value="it">IT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class Level</label>
                    <select name="class_level" id="classLevelSelect" onchange="updateFee()" required>
                        <option value="">Select Level</option>
                        <?php foreach($class_levels as $level): ?>
                            <option value="<?php echo $level; ?>"><?php echo $level; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                    </select>
                </div>
                
                <div class="form-group" style="justify-content: center;">
                    <div class="option-group">
                        <input type="checkbox" name="generate_bill_opt" id="generateBillOpt" checked>
                        <label for="generateBillOpt">Generate & Print Bill Receipt</label>
                    </div>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-title">Payment Summary</div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Total Amount (Rs.)</label>
                    <input type="number" name="total_amount" id="totalInput" value="0.00" oninput="calc()" step="0.01" style="font-size:24px; font-weight:800; color:var(--primary);">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Paid Amount (Rs.)</label>
                    <input type="number" id="paid" name="paid_amount" placeholder="Enter Paid Amount" oninput="calc()" step="0.01" required>
                </div>
                <div class="summary-row" style="margin-top:22px;">
                    <div class="summary-label">Balance</div>
                    <div class="summary-value">
                        Rs. <span id="balance">0.00</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" name="submit_payment">
                <i data-lucide="database"></i> Save Class Payment
            </button>
        </form>
    </div>
</div>

<script>
lucide.createIcons();
const feesData = <?php echo json_encode($fees_data); ?>;

// Time and Date
document.addEventListener("DOMContentLoaded", function() {
    function updateDateTime(){
        const now = new Date();
        document.getElementById('liveTime').innerHTML = now.toLocaleTimeString('en-US',{ hour:'2-digit', minute:'2-digit', second:'2-digit' });
        document.getElementById('liveDate').innerHTML = now.toLocaleDateString('en-US',{ weekday:'long', year:'numeric', month:'long', day:'numeric' });
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();
});

function updateFee() {
    let type = document.getElementById('categorySelect').value.toLowerCase().trim();
    let level = document.getElementById('classLevelSelect').value.trim();
    let price = 0;

    if(type && level && feesData[type]) {
        for (let key in feesData[type]) {
            if (key.includes(level)) {
                price = parseFloat(feesData[type][key]);
                break;
            }
        }
    }
    document.getElementById('totalInput').value = price.toFixed(2);
    calc(); 
}

function calc() {
    let total = parseFloat(document.getElementById('totalInput').value || 0);
    let paid = parseFloat(document.getElementById('paid').value || 0);
    let balance = paid - total;
    document.getElementById('balance').innerText = balance.toFixed(2);
}

function fetchStudentName() {
    let studentId = document.getElementById('studentIdInput').value.trim();
    if (studentId.length > 2) {
        fetch('fetch_student.php?student_id=' + encodeURIComponent(studentId))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.found) {
                    document.getElementById('studentNameInput').value = data.name;
                } else {
                    document.getElementById('studentNameInput').value = "";
                }
            });
    }
}
</script>

<?php if($show_receipt): ?>
<div id="receipt-print-area">
    <div class="receipt-header">
        <h2>GLOBAL INSTITUTE</h2>
        <p style="font-size: 11px; margin-top: 3px;">English & IT Studies Center</p>
    </div>

    <div class="receipt-info">
        <div class="receipt-row"><span>Inv No:</span> <strong>#<?= $receipt_data['id'] ?></strong></div>
        <div class="receipt-row"><span>Date:</span> <?= $receipt_data['date'] ?></div>
        <div class="receipt-row"><span>Time:</span> <?= $receipt_data['time'] ?></div>
        <div class="receipt-row"><span>Method:</span> <?= $receipt_data['method'] ?></div>
    </div>

    <div style="margin: 10px 0;">
        <div class="receipt-row"><span>Student ID:</span> <span><?= htmlspecialchars($student_id) ?></span></div>
        <div class="receipt-row"><span>Name:</span> <strong><?= htmlspecialchars($receipt_data['name']) ?></strong></div>
        <div class="receipt-row"><span>Course:</span> <span><?= htmlspecialchars($receipt_data['class']) ?></span></div>
    </div>

    <div class="receipt-total">
        <div class="receipt-row"><span>Total Fee:</span> <span>Rs. <?= number_format($receipt_data['total'], 2) ?></span></div>
        <div class="receipt-row"><span>Amount Paid:</span> <strong>Rs. <?= number_format($receipt_data['amount'], 2) ?></strong></div>
        <div class="receipt-row" style="border-top: 1px dotted #000; padding-top: 5px; margin-top: 5px;">
            <span>Balance Due:</span> <strong>Rs. <?= number_format($receipt_data['balance'], 2) ?></strong>
        </div>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your payment!</p>
        <p style="margin-top: 15px;">......................................</p>
        <p>Authorized Signature</p>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    };
</script>
<?php endif; ?>

</body>
</html>