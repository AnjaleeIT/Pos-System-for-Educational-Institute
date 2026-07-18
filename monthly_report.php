<?php
session_start();
require 'config.php';

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$sql = "SELECT p.*, s.name AS student_name 
        FROM class_payments p 
        LEFT JOIN students s ON p.student_id = s.student_id 
        WHERE DATE_FORMAT(p.payment_date, '%Y-%m') = '$selected_month'
        ORDER BY p.payment_date DESC";

$result = $conn->query($sql);
$total_income = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Report</title>
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
        .page-title { font-size: 34px; font-weight: 900; letter-spacing: -1px; margin-bottom: 30px; }

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
        
        /* Total Row Highlight */
        .total-row td { background: rgba(37,99,235,0.1) !important; color: var(--primary); font-weight: 800; font-size: 1.1rem; }

        @media(max-width:768px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Monthly Report - <?php echo htmlspecialchars($selected_month); ?></h1>
        
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student Name</th>
                        <th style="text-align:right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $total_income += $row['paid_amount'];
                    ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo $row['payment_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['student_name'] ?? 'Unknown'); ?></td>
                        <td style="text-align:right; font-weight:700;">Rs. <?php echo number_format($row['paid_amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <tr class="total-row">
                        <td colspan="2" style="border-radius: 12px 0 0 12px;">Total Income</td>
                        <td style="text-align:right; border-radius: 0 12px 12px 0;">Rs. <?php echo number_format($total_income, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>