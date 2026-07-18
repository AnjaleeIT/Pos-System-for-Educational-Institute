<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar" class="sidebar">

    <div class="brand">
        <img src="logo1111111.png" alt="Logo" class="brand-logo">
        <div class="title">
            <div class="name">Global Institute</div>
            <div class="sub">Admin Panel</div>
        </div>
    </div>

    <nav class="nav">
        <a href="admin_dashboard.php" class="<?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <a href="reports.php" class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
            <i data-lucide="bar-chart-3"></i>
            <span>Reports</span>
        </a>

        <a href="payment_setup.php" class="<?php echo ($current_page == 'payment_setup.php') ? 'active' : ''; ?>">
            <i data-lucide="wallet"></i>
            <span>Class Fee Setup</span>
        </a>

        <a href="system_control.php" class="<?php echo ($current_page == 'system_control.php') ? 'active' : ''; ?>">
            <i data-lucide="settings"></i>
            <span>System Control</span>
        </a>

        <a href="change_password.php" class="<?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>">
            <i data-lucide="lock"></i>
            <span>Change Password</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <span class="label">Signed in as</span>
            <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></strong>
        </div>

        <a href="logout.php" class="logout-btn">
            <i data-lucide="log-out"></i>
            <span>Logout</span>
        </a>
    </div>

</aside>

<style>
/* Modern Professional Variables */
:root {
    --sidebar-bg: #ffffff;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --primary: #2563eb;
    --primary-light: #d4e5fa;
    --hover-bg: #f8fafc;
    --border-color: #e2e8f0;
    --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.sidebar {
    width: 280px;
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    display: flex;
    flex-direction: column;
    padding: 24px;
    border-right: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

/* BRAND */
.brand {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
    padding: 0 10px;
}

.brand-logo {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #f1f5f9;
    padding: 5px;
}

.title .name {
    font-weight: 700;
    font-size: 18px;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.title .sub {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

/* NAV */
.nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 12px;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav a i {
    width: 20px;
    height: 20px;
}

.nav a:hover {
    background: var(--hover-bg);
    color: var(--text-main);
}

.nav a.active {
    background: var(--primary-light);
    color: var(--primary);
    border-left: 4px solid var(--primary);
    border-radius: 0 12px 12px 0;
}

/* FOOTER */
.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-info {
    font-size: 13px;
    text-align: center;
    color: var(--text-muted);
}

.user-info .label {
    display: block;
    margin-bottom: 2px;
}

.user-info strong {
    color: var(--text-main);
    display: block;
    font-size: 14px;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: #fddada;
    padding: 12px;
    border-radius: 12px;
    color: #ef4444;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.logout-btn:hover {
    background: #fee2e2;
}
</style>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>