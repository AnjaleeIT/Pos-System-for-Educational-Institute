<?php
session_start();
require 'config.php';

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php"); exit;
}

$show_receipt = false; 
$receipt_data = [];

// Form Submission Logic
if (isset($_POST['create_bill'])) {
    $student_id = $_POST['student_id'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $class_name = $_POST['class_name'] ?? '';
    $amount = $_POST['paid_amount'] ?? 0;
    
    // (Check if student exists)
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    // Add to students table if not exists)
    if ($result->num_rows == 0) {
        $insert_student = $conn->prepare("INSERT INTO students (student_id, name, registered_date, class_name) VALUES (?, ?, NOW(), ?)");
        $insert_student->bind_param("sss", $student_id, $student_name, $class_name);
        $insert_student->execute();
    }

   
    $stmt = $conn->prepare("INSERT INTO class_payments (student_id, student_name, class_name, paid_amount, payment_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssd", $student_id, $student_name, $class_name, $amount);
    
    if ($stmt->execute()) {
        $show_receipt = true;
        $receipt_data = [
            'id' => $conn->insert_id, 
            'name' => $student_name, 
            'class' => $class_name, 
            'amount' => $amount, 
            'date' => date('Y-m-d'),
            'time' => date('H:i:s')
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Bill - Global Institute</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{ --bg:#eef4ff; --bg2:#dbeafe; --card:#ffffff; --text:#0f172a; --muted:#64748b; --primary:#2563eb; --primary2:#1d4ed8; --border:#dbe4f0; --shadow: 0 10px 30px rgba(37,99,235,.08); --radius:18px; }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Inter',sans-serif; background: #eaf4ff; color:var(--text); display:flex; min-height:100vh; }
        
        .main-content{ margin-left:300px; width:calc(100% - 300px); padding:30px; }
        .header-area{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .header-left{ display:flex; flex-direction:column; gap:10px; }
        .top-welcome{ font-size:14px; color:var(--muted); font-weight:600; }
        .page-title{ font-size:34px; font-weight:900; color:#0f172a; letter-spacing:-1px; }
        
        .date-time-box{ background:white; border:1px solid var(--border); padding:16px 24px; border-radius:18px; box-shadow:var(--shadow); min-width:230px; }
        .time{ font-size:28px; font-weight:800; color:var(--primary); }
        .date{ font-size:14px; margin-top:4px; color:var(--muted); font-weight:600; }
        
        /* Panel */
        .panel{ background:white; border-radius:24px; padding:30px; border:1px solid var(--border); box-shadow:var(--shadow); max-width:600px; }
        .form-group{ margin-bottom: 20px; }
        label{ display:block; margin-bottom:8px; font-weight:600; color:var(--text); }
        .form-input{ width:100%; padding:14px; border:1px solid var(--border); border-radius:12px; font-size: 15px; outline: none; }
        .form-input:focus { border-color: var(--primary); }
        .btn-submit{ width:100%; padding:16px; background:var(--primary); color:#fff; border:none; border-radius:12px; font-weight:700; cursor:pointer; font-size: 16px; transition: 0.2s; }
        .btn-submit:hover { background: var(--primary2); }

        /* Print Styles - Professional Receipt */
        #receipt-print-area { display: none; }

        @media print {
            body * { display: none; }
            #receipt-print-area, #receipt-print-area * { display: block !important; }
            
            #receipt-print-area {
                width: 80mm;
                margin: 0 auto;
                padding: 10px;
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #000;
            }
            .receipt-header { text-align: center; margin-bottom: 15px; }
            .receipt-header h2 { font-size: 16px; margin: 0; }
            .receipt-info { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; margin: 10px 0; font-size: 13px; }
            .receipt-row { display: flex; justify-content: space-between; margin: 4px 0; }
            .receipt-total { font-size: 16px; font-weight: bold; margin-top: 10px; text-align: right; }
            .receipt-footer { text-align: center; margin-top: 20px; font-size: 11px; }
        }
    </style>
</head>
<body>

<?php include 'coordinator_sidebar.php'; ?>

<div class="main-content">
    <header class="header-area">
        <div class="header-left">
            <div class="top-welcome">Billing System</div>
            <h1 class="page-title">Generate New Bill</h1>
        </div>
        <div class="date-time-box">
            <div class="time" id="liveTime">00:00:00</div>
            <div class="date" id="liveDate">Loading...</div>
        </div>
    </header>

    <div class="panel">
        <form method="POST">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" id="student_id" class="form-input" onblur="fetchStudent()" required>
            </div>
            <div class="form-group">
                <label>Student Name</label>
                <input type="text" name="student_name" id="student_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label>Course</label>
                <select name="class_name" class="form-input" required>
                    <option value="English">English</option>
                    <option value="IT">IT</option>
                </select>
            </div>
            <div class="form-group">
                <label>Amount (Rs.)</label>
                <input type="number" name="paid_amount" class="form-input" required>
            </div>
            <button type="submit" name="create_bill" class="btn-submit">Generate & Print</button>
        </form>
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

function fetchStudent() {
    let id = document.getElementById('student_id').value;
    if (id === "") return;
    fetch('get_student.php?student_id=' + id)
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('student_name').value = data.name;
            // Name found: allow editing if needed, but it's already filled
        } else {
            // Student not found: Ask to enter name manually
            document.getElementById('student_name').value = "";
            document.getElementById('student_name').focus();
        }
    });
}
</script>

<?php if($show_receipt): ?>
<div id="receipt-print-area">
    <div class="receipt-header">
        <h2>GLOBAL INSTITUTE</h2>
        <p style="font-size: 11px;">English & IT Studies</p>
    </div>

    <div class="receipt-info">
        <div class="receipt-row"><span>Inv No:</span> <strong>#<?= $receipt_data['id'] ?></strong></div>
        <div class="receipt-row"><span>Date:</span> <?= $receipt_data['date'] ?></div>
        <div class="receipt-row"><span>Time:</span> <?= $receipt_data['time'] ?></div>
    </div>

    <div style="margin: 10px 0; font-size: 13px;">
        <div class="receipt-row"><span>Student:</span> <strong><?= htmlspecialchars($receipt_data['name']) ?></strong></div>
        <div class="receipt-row"><span>Course:</span> <strong><?= htmlspecialchars($receipt_data['class']) ?></strong></div>
    </div>

    <div class="receipt-total">
        TOTAL PAID: Rs. <?= number_format($receipt_data['amount'], 2) ?>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your payment!</p>
        <p>......................................</p>
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