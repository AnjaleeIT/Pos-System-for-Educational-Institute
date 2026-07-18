<?php
session_start();
require 'config.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// SQL Query 
$sql = "SELECT p.*, s.name AS student_name 
        FROM class_payments p 
        LEFT JOIN students s ON p.student_id = s.student_id 
        WHERE DATE(p.payment_date) = '$date'
        ORDER BY p.id ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { 
            --bg: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #f8fbff 100%); 
            --card: rgba(255,255,255,.78); 
            --primary: #2563eb; 
            --text: #0f172a; 
            --muted: #64748b;
            --shadow: 0 10px 30px rgba(37,99,235,.08);
            --radius: 24px;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }
        
        .main-content { margin-left: 300px; width: calc(100% - 300px); padding: 40px; }
        
        /* Header */
        .header-area { margin-bottom: 30px; }
        .page-title { font-size: 34px; font-weight: 900; letter-spacing: -1px; }

        /* Glass Card */
        .card { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            border: 1px solid rgba(255,255,255,.7); 
            padding: 30px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
        }
        
        /* Table Styling */
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-top: 10px; }
        th { text-align: left; padding: 15px; color: var(--muted); font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 15px; background: rgba(255,255,255,.3); }
        tr td:first-child { border-radius: 12px 0 0 12px; }
        tr td:last-child { border-radius: 0 12px 12px 0; }
        
        @media(max-width:768px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="header-area">
            <div style="color:var(--muted); font-weight:600;">Report</div>
            <h1 class="page-title">Daily Report - <?php echo htmlspecialchars($date); ?></h1>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th style="text-align:right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($row['student_name'] ?? 'Unknown'); ?></td>
                        <td style="text-align:right; font-weight:700; color:var(--primary);">Rs. <?php echo number_format($row['paid_amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>