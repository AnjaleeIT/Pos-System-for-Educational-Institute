<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

require 'config.php';

// Filter Logic
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$filter_type = $_GET['type'] ?? 'all';

$sql = "SELECT * FROM class_payments WHERE DATE(payment_date) BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($filter_type != 'all') {
    $sql .= " AND payment_category = ?";
    $params[] = $filter_type;
}
$sql .= " ORDER BY payment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports - Admin Panel</title>
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
        
        .main-content { margin-left: 300px; flex: 1; padding: 40px; }
        
        /* Header */
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 34px; font-weight: 900; letter-spacing: -1px; }
        .header-actions { display: flex; gap: 10px; }
        
        .theme-toggle { background: var(--card); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.8); padding: 10px 15px; border-radius: 12px; cursor: pointer; color: var(--primary); }
        
        /* Cards */
        .card { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            border: 1px solid rgba(255,255,255,.7); 
            padding: 25px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
            margin-bottom: 25px; 
        }
        
        /* Filter Form */
        .filter-form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        input, select { padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; background: rgba(255,255,255,.5); }
        
        /* Table */
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { text-align: left; padding: 15px; color: var(--muted); font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 15px; background: rgba(255,255,255,.3); }
        tr td:first-child { border-radius: 12px 0 0 12px; }
        tr td:last-child { border-radius: 0 12px 12px 0; }
        
        .btn-action { background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 12px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-action:hover { background: #1d4ed8; }

        @media(max-width:768px){ .main-content { margin-left: 0; padding: 20px; } }

        /* Print Logic */
        @media print {
            .sidebar, .header-area, .theme-toggle, .filter-form, .btn-action { display: none !important; }
            body { background: white !important; }
            .card { background: white !important; box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <header class="header-area">
        <div>
            
            <h1 class="page-title">Financial Report</h1>
        </div>
        <div class="header-actions">
            <button class="theme-toggle" id="themeToggle">
                <i id="sunIcon" data-lucide="sun"></i>
                <i id="moonIcon" data-lucide="moon" style="display:none;"></i>
            </button>
        </div>
    </header>

    <div class="card">
        <form method="GET" class="filter-form">
            <input type="date" name="start_date" value="<?php echo $start_date; ?>">
            <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            <select name="type">
                <option value="all">All Categories</option>
                <option value="Internal" <?php if($filter_type == 'Internal') echo 'selected'; ?>>Internal</option>
                <option value="External" <?php if($filter_type == 'External') echo 'selected'; ?>>External</option>
            </select>
            <button type="submit" class="btn-action">Filter</button>
            <button type="button" class="btn-action" style="background:#64748b;" onclick="window.print()">Print Report</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Category</th>
                    <th>Amount (Rs)</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="font-weight:600;"><?php echo $row['payment_date']; ?></td>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo $row['payment_category']; ?></td>
                    <td style="font-weight:700; color:var(--primary);">Rs. <?php echo number_format($row['paid_amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();
    
    // Theme Toggle Logic
    const themeBtn = document.getElementById('themeToggle');
    const sunIcon = document.getElementById('sunIcon');
    const moonIcon = document.getElementById('moonIcon');

    themeBtn.addEventListener('click', () => {
        document.body.classList.toggle('light-theme');
        const isLight = document.body.classList.contains('light-theme');
        sunIcon.style.display = isLight ? 'none' : 'block';
        moonIcon.style.display = isLight ? 'block' : 'none';
    });
</script>
</body>
</html>