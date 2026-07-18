<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar" class="sidebar">
    <div class="sidebar-glow"></div>

    <div class="brand">
        <div class="logo-wrapper">
            <img src="logo1111111.png" alt="Logo" class="brand-logo">
        </div>
        <div class="title">
            <div class="name">Global Institute</div>
            <div class="sub">Coordinator Panel</div>
        </div>
    </div>

    <nav class="nav">
        <?php 
        $menu = [
            ['link' => 'coordinator_dashboard.php', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ['link' => 'class_payment.php', 'icon' => 'graduation-cap', 'label' => 'Class Payment'],
            ['link' => 'payment_summary.php', 'icon' => 'history', 'label' => 'Payment Summary'],
            ['link' => 'coordinator_reports.php', 'icon' => 'bar-chart-3', 'label' => 'Reports'],
            
        ];
        
        foreach($menu as $item): 
            $active = ($current_page == $item['link']) ? 'active' : '';
        ?>
            <a href="<?php echo $item['link']; ?>" class="<?php echo $active; ?>">
                <i data-lucide="<?php echo $item['icon']; ?>"></i>
                <span><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-box">
            <div class="user-avatar">
                <?php echo strtoupper(substr(isset($_SESSION['name']) ? $_SESSION['name'] : 'C', 0, 1)); ?>
            </div>
            <div class="user-info">
                <small>Signed in as</small>
                <strong><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Coordinator'; ?></strong>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">
            <i data-lucide="log-out"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<style>
/* Modern UI Variables */
:root {
    --bg-sidebar: rgba(255, 255, 255, 0.95);
    --primary: #2563eb;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --active-bg: #eff6ff;
}

.sidebar {
    width: 290px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 24px;
    background: var(--bg-sidebar);
    backdrop-filter: blur(12px);
    border-right: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

/* Brand Section */
.brand {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 40px;
}

.logo-wrapper {
    width: 60px;
    height: 70px;
    background: #ffffff;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.brand-logo { width: 80%; height: 80%; object-fit: contain; }

.title .name { font-weight: 700; color: var(--text-main); font-size: 18px; }
.title .sub { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); font-weight: 600; }

/* Navigation */
.nav { display: flex; flex-direction: column; gap: 8px; }

.nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 12px;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav a:hover {
    background: var(--active-bg);
    color: var(--primary);
}

/* Modern Active State */
.nav a.active {
    background: var(--active-bg);
    color: var(--primary);
    border-left: 4px solid var(--primary);
    border-radius: 0 12px 12px 0;
}

.nav a i { width: 20px; height: 20px; }

/* Footer */
.sidebar-footer { margin-top: auto; padding-top: 20px; }

.user-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 14px;
    margin-bottom: 16px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.user-info small { font-size: 10px; color: var(--text-muted); }
.user-info strong { display: block; font-size: 13px; color: var(--text-main); }

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    background: #fef2f2;
    color: #dc2626;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}

.logout-btn:hover { background: #fee2e2; }

@media(max-width:768px) { .sidebar { transform: translateX(-100%); } }
</style>