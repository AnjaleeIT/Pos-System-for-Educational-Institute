<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'coordinator') {
    header("Location: index.php");
    exit;
}

require 'config.php';
include 'system_status_check.php';

// Load school payment values
$grades = [];
$res = $conn->query("SELECT name, amount FROM payment_settings WHERE type='school'");
while ($row = $res->fetch_assoc()) {
    $grades[$row['name']] = $row['amount'];
}

// Handle payment form (UNCHANGED PHP LOGIC)
if (isset($_POST['generate'])) {
    $student_id = trim($_POST['student_id']);
    $grade = trim($_POST['grade']);
    $subject = trim($_POST['subject']);
    $haveCash = floatval($_POST['haveCash']);
    $payCash = floatval($_POST['payCash']);
    $remaining = floatval($_POST['remaining']); // This value comes from the JS-updated field
    $balance = $haveCash - $payCash;

    $sql = "INSERT INTO payments (student_id, payment_type, category, paid_amount, balance)
            VALUES (?, 'Internal', 'School', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idd", $student_id, $payCash, $balance);
    $stmt->execute();
    $payment_id = $conn->insert_id;

    // The $remaining variable here is the one from $_POST, which was updated by JS
    header("Location: school_bill.php?id=$payment_id&student=$student_id&grade=$grade&subject=$subject&have=$haveCash&pay=$payCash&balance=$balance&remaining=$remaining");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>School Payment - Coordinator</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* --- CSS Variables (Light Mode Default) --- */
:root {
    --bg-body: #f4f7f9;
    --bg-sidebar: #ffffff;
    --bg-card: #ffffff;
    --bg-form-input: #f9fafb;
    --color-text-primary: #111827;
    --color-text-secondary: #6b7280;
    --color-text-header: #111827;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --color-primary: #3b82f6;
    --color-primary-hover: #2563eb;
    --color-primary-light-bg: #eff6ff;
}

/* --- Dark Mode Variables --- */
.dark-mode {
    --bg-body: #111827;
    --bg-sidebar: #1f2937;
    --bg-card: #1f2937;
    --bg-form-input: #374151;
    --color-text-primary: #f9fafb;
    --color-text-secondary: #9ca3af;
    --color-text-header: #f9fafb;
    --border-color: #374151;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.15);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.12);
    --color-primary: #3b82f6;
    --color-primary-hover: #60a5fa;
    --color-primary-light-bg: #2b3a4f;
}

/* --- Global Styles --- */
*, *::before, *::after {
    box-sizing: border-box;
}
body {
    margin: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background: var(--bg-body);
    color: var(--color-text-primary);
    line-height: 1.6;
    transition: background-color 0.3s, color 0.3s;
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* --- Sidebar --- */
.sidebar {
    width: 260px;
    background: var(--bg-sidebar);
    border-right: 1px solid var(--border-color);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    transition: background-color 0.3s, border-color 0.3s;
    height: 100vh;
    position: fixed;
}
.sidebar-header {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1rem;
}
.sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0;
    flex-grow: 1;
}
.sidebar-nav li a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    color: var(--color-text-secondary);
    transition: background-color 0.2s, color 0.2s;
}
.sidebar-nav li a:hover {
    background: var(--bg-body);
    color: var(--color-text-primary);
}
.sidebar-nav li a.active {
    background: var(--color-primary-light-bg);
    color: var(--color-primary);
    font-weight: 600;
}
.sidebar-footer {
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}
.sidebar-footer a {
    display: block;
    text-align: center;
    padding: 0.75rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    background-color: #ef4444;
    color: #ffffff;
    transition: background-color 0.2s;
}
.sidebar-footer a:hover {
    background-color: #dc2626;
}

/* --- Main Content --- */
.main-content {
    flex-grow: 1;
    margin-left: 260px; /* Same as sidebar width */
    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* --- Main Header (Top Bar) --- */
.main-header {
    background: var(--bg-card);
    padding: 1rem 2rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
    transition: background-color 0.3s, border-color 0.3s;
}
.main-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--color-text-primary);
}

.toggle-btn {
    background: transparent;
    border: 1px solid transparent;
    color: var(--color-text-secondary);
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background 0.3s, color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.toggle-btn:hover {
    background: var(--bg-body);
    color: var(--color-text-primary);
}

/* --- Page Content (Form Area) --- */
.page-content {
    flex-grow: 1;
    padding: 2rem;
    overflow-y: auto;
}
.payment-form-container {
    max-width: 900px;
    margin: 0 auto;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    transition: background-color 0.3s, border-color 0.3s;
}
.payment-form-container h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border-color);
}

form {
    padding: 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.form-group {
    display: flex;
    flex-direction: column;
}
/* Class to make a form group span both columns */
.form-span-2 {
    grid-column: span 2 / span 2;
}

label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color-text-secondary);
    font-size: 0.875rem; /* 14px */
}
input[type="text"],
input[type="number"],
select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-form-input);
    color: var(--color-text-primary);
    font-family: 'Inter', sans-serif;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.3s;
}
input:focus, select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}
input[readonly] {
    background: var(--bg-body);
    color: var(--color-text-secondary);
    cursor: not-allowed;
}

.dashboard-btn {
    border: none;
    padding: 0.85rem 1.5rem;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
    text-align: center;
    background: var(--color-primary);
    color: white;
}
.dashboard-btn:hover {
    background: var(--color-primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* --- Responsiveness --- */
@media (max-width: 900px) {
    body {
        display: block; /* Stack sidebar and main content */
    }
    .sidebar {
        width: 100%;
        height: auto;
        position: static; /* Change from fixed to static */
        border-right: none;
        border-bottom: 1px solid var(--border-color);
    }
    .main-content {
        margin-left: 0;
        height: auto;
    }
    .page-content {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    form {
        grid-template-columns: 1fr; /* Stack form fields */
    }
    .form-span-2 {
        grid-column: span 1 / span 1; /* Reset span */
    }
    .main-header {
        flex-direction: column;
        gap: 0.5rem;
        padding: 1rem;
    }
    .page-content {
        padding: 1rem;
    }
    form {
        padding: 1.5rem;
    }
}
</style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        Coordinator Panel
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="coordinator_dashboard.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Dashboard
            </a>
        </li>
        <li>
            <a href="school_payment.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                School Payment
            </a>
        </li>
        <li>
            <a href="external_payment.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                External Payment
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php">Logout</a>
    </div>
</aside>

<div class="main-content">
    
    <header class="main-header">
        <h1>Student School Payment</h1>
        
        <button id="theme-toggle" class="toggle-btn" aria-label="Toggle dark mode">
            <svg id="sun-icon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6.364 6.364 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>
    </header>

    <main class="page-content">
        <div class="payment-form-container">
            <h3>Payment Details</h3>
            
            <form method="POST">
                
                <div class="form-group form-span-2">
                    <label for="student_id">Student ID:</label>
                    <input type="text" id="student_id" name="student_id" placeholder="Enter Student ID" required>
                </div>

                <div class="form-group">
                    <label for="grade">Select Grade:</label>
                    <select name="grade" id="grade" onchange="updatePayment()" required>
                        <option value="">Select Grade</option>
                        <?php
                        // (UNCHANGED PHP)
                        foreach ($grades as $name => $amount) {
                            echo "<option value='$name' data-amount='$amount'>$name (Rs. $amount)</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subject">Select Subject:</label>
                    <select name="subject" id="subject" required>
                        <option value="">Select Subject</option>
                        <option>ICT</option>
                        <option>Maths</option>
                        <option>Science</option>
                        <option>English</option>
                        <option>Sinhala</option>
                    </select>
                </div>
                
                <div class="form-group form-span-2">
                    <label for="remaining">Grade Total Fee / Remaining:</label>
                    <input type="number" step="0.01" id="remaining" name="remaining" readonly required>
                </div>
                
                <div class="form-group">
                    <label for="haveCash">Current Cash (X):</label>
                    <input type="number" step="0.01" id="haveCash" name="haveCash" oninput="calculateValues()" required>
                </div>

                <div class="form-group">
                    <label for="payCash">Paying Now (Y):</label>
                    <input type="number" step="0.01" id="payCash" name="payCash" oninput="calculateValues()" required>
                </div>

                <div class="form-group form-span-2">
                    <label for="balance">Balance (X - Y):</label>
                    <input type="text" id="balance" name="balance" readonly>
                </div>

                <div class="form-group form-span-2">
                    <button type="submit" name="generate" class="dashboard-btn">Generate Bill</button>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
function updatePayment() {
    let sel = document.getElementById("grade");
    let amount = sel.options[sel.selectedIndex].getAttribute("data-amount");
    if (amount) {
        document.getElementById("remaining").value = parseFloat(amount).toFixed(2);
        // Also recalculate if values are already present
        calculateValues();
    }
}

function calculateValues() {
    let x = parseFloat(document.getElementById("haveCash").value) || 0;
    let y = parseFloat(document.getElementById("payCash").value) || 0;
    
   
    let sel = document.getElementById("grade");
    let originalFee = parseFloat(sel.options[sel.selectedIndex].getAttribute("data-amount")) || 0;
    
    // If no grade is selected, fall back to the value in the 'remaining' box (less reliable)
    if (originalFee === 0) {
         originalFee = parseFloat(document.getElementById("remaining").value) || 0;
    }

    let balance = x - y;
    let newRemaining = originalFee - y;
    
    document.getElementById("balance").value = balance.toFixed(2);
    // Update the 'remaining' field to show the new amount owed
    document.getElementById("remaining").value = newRemaining.toFixed(2);
}

// --- Theme Toggle JavaScript (From Admin Dashboard) ---
/**
 * Applies the selected theme (dark/light) to the body and saves preference.
 */
function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.body.classList.toggle('dark-mode', isDark);

    const sunIcon = document.getElementById('sun-icon');
    const moonIcon = document.getElementById('moon-icon');
    
    if (sunIcon && moonIcon) {
        sunIcon.style.display = isDark ? 'block' : 'none';
        moonIcon.style.display = isDark ? 'none' : 'block';
    }
}

// --- Initialization ---
const savedTheme = localStorage.getItem('theme');
const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
const initialTheme = savedTheme || systemPreference;
applyTheme(initialTheme); 

// --- Event Listeners ---
document.getElementById('theme-toggle').addEventListener('click', () => {
    const currentTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
});

// Optional: Listen for system theme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        applyTheme(e.matches ? 'dark' : 'light');
    }
});
</script>
</body>
</html>