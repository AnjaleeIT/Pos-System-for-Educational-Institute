<?php
session_start();
require 'config.php';

// --- DELETE LOGIC ---
if (isset($_POST['delete_id'])) {
    $del_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM payment_settings WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
}

// --- ADD NEW ITEM LOGIC ---
if (isset($_POST['add_item'])) {
    $type = $_POST['new_type'];
    $category = $_POST['new_category'];
    $name = trim($_POST['new_name']);
    $amount = $_POST['new_amount'];

    if (!empty($name) && is_numeric($amount)) {
        $stmt = $conn->prepare("INSERT INTO payment_settings (type, category, name, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $type, $category, $name, $amount);
        $stmt->execute();
    }
}

// --- FETCH & GROUP DATA ---
$result = $conn->query("SELECT * FROM payment_settings ORDER BY category ASC, name ASC");
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Setup</title>
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

        /* Modern Glass Card */
        .card { 
            background: var(--card); 
            backdrop-filter: blur(14px); 
            border: 1px solid rgba(255,255,255,.7); 
            padding: 30px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
            margin-bottom: 25px; 
        }
        
        .card-header { font-size: 1.2rem; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--primary); }
        
        /* Form Styling */
        .form-grid { display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 15px; align-items: end; }
        .input-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--muted); }
        .form-input, .form-select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; background: rgba(255,255,255,.5); }
        
        .btn-add { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-add:hover { background: #1d4ed8; }

        /* Table Styling */
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { text-align: left; padding: 15px; color: var(--muted); font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 15px; background: rgba(255,255,255,.3); }
        tr td:first-child { border-radius: 12px 0 0 12px; }
        tr td:last-child { border-radius: 0 12px 12px 0; }
        
        .btn-del { background: #fee2e2; color: #ef4444; border: none; padding: 8px; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .btn-del:hover { background: #fecaca; }
        
        @media(max-width: 992px) { .form-grid { grid-template-columns: 1fr; } }
        @media(max-width:768px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header-area">
        <h1 class="page-title">Payment Setup</h1>
    </div>

    <div class="card">
        <div class="card-header"><i data-lucide="plus-circle"></i> Add New Fee Item</div>
        <form method="POST">
            <div class="form-grid">
                <div class="input-group">
                    <label>Type</label>
                    <select name="new_type" class="form-select">
                        <option value="course">Course</option>
                        <option value="school">School Grade</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Category</label>
                    <select name="new_category" class="form-select">
                        <option value="English">English</option>
                        <option value="IT">IT</option>
                        <option value="General">General</option>
                        <option value="School">School</option>
                    </select>
                </div>
                <div class="input-group" style="grid-column: span 2;">
                    <label>Name</label>
                    <input type="text" name="new_name" class="form-input" placeholder="e.g. Graphic Design" required>
                </div>
                <div class="input-group">
                    <label>Fee (Rs)</label>
                    <input type="number" name="new_amount" class="form-input" placeholder="0.00" required>
                </div>
                <button type="submit" name="add_item" class="btn-add">Add Item</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><i data-lucide="list"></i> Current Fee Structure</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Name</th>
                    <th style="text-align:right">Fee (Rs)</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($item['category']); ?></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td style="text-align:right; font-weight: 700;">Rs. <?php echo number_format($item['amount'], 2); ?></td>
                    <td style="text-align:center;">
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="delete_id" value="<?php echo $item['id']; ?>" class="btn-del" onclick="return confirm('Are you sure?');">
                                <i data-lucide="trash-2" width="16"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>