<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// 1. Get current system status
$statusQuery = "SELECT system_status FROM system_control WHERE id = 1";
$statusResult = $conn->query($statusQuery);
$statusRow = $statusResult->fetch_assoc();
$currentStatus = $statusRow ? $statusRow['system_status'] : 'open';

// 2. Change status logic
$msg = "";
if (isset($_POST['action'])) {
    $newStatus = ($_POST['action'] == 'block') ? 'blocked' : 'open';
    $update = "UPDATE system_control SET system_status = ? WHERE id = 1";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("s", $newStatus);
    
    if($stmt->execute()){
        $currentStatus = $newStatus;
        $msg = ($newStatus == 'open') ? "System Activated Successfully" : "System Blocked Successfully";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Control - Admin Panel</title>
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
        .control-container { display: flex; justify-content: center; align-items: center; min-height: 60vh; }
        .status-card { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            border: 1px solid rgba(255,255,255,.7); 
            padding: 50px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
            text-align: center; 
            max-width: 500px; 
            width: 100%; 
        }

        .icon-wrapper {
            width: 90px; height: 90px; border-radius: 50%; margin: 0 auto 25px;
            display: flex; align-items: center; justify-content: center;
        }
        
        /* Status Colors */
        .status-card.open .icon-wrapper { background: #dcfce7; color: #16a34a; }
        .status-card.blocked .icon-wrapper { background: #fee2e2; color: #dc2626; }

        .status-desc { color: var(--muted); margin: 15px 0 30px; font-weight: 500; }

        .btn-control {
            padding: 14px 35px; border: none; border-radius: 14px; font-size: 16px; font-weight: 700;
            cursor: pointer; color: white; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .btn-control:hover { opacity: 0.9; transform: translateY(-2px); }
        .btn-block { background: #dc2626; }
        .btn-open { background: #16a34a; }

        .alert-box {
            background: rgba(37,99,235,0.1); color: var(--primary); padding: 15px;
            border-radius: 12px; margin-bottom: 25px; font-weight: 600;
        }

        @media(max-width:768px){ .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header-area">
        <h1 class="page-title">System Control</h1>
    </div>

    <div class="control-container">
        <div class="status-card <?php echo $currentStatus; ?>">
            
            <?php if ($msg): ?>
                <div class="alert-box"><?php echo $msg; ?></div>
            <?php endif; ?>

            <div class="icon-wrapper">
                <?php if ($currentStatus == 'open'): ?>
                    <i data-lucide="check-circle" width="45" height="45"></i>
                <?php else: ?>
                    <i data-lucide="lock" width="45" height="45"></i>
                <?php endif; ?>
            </div>
            
            <h2 style="font-size: 24px; margin-bottom: 10px;">System is <?php echo ($currentStatus == 'open') ? 'ACTIVE' : 'BLOCKED'; ?></h2>
            <p class="status-desc">
                <?php if ($currentStatus == 'open'): ?>
                    The system is currently active for all coordinators.
                <?php else: ?>
                    The system is currently locked. Coordinator access is restricted.
                <?php endif; ?>
            </p>

            <form method="POST">
                <?php if ($currentStatus == 'open'): ?>
                    <button type="submit" name="action" value="block" class="btn-control btn-block">
                        <i data-lucide="lock" width="20"></i> Block System
                    </button>
                <?php else: ?>
                    <button type="submit" name="action" value="open" class="btn-control btn-open">
                        <i data-lucide="unlock" width="20"></i> Re-Activate
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>