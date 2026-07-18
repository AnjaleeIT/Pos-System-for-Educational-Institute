<?php
require 'config.php';

$statusCheck = $conn->query("SELECT system_status FROM system_control WHERE id = 1");
$statusRow = $statusCheck->fetch_assoc();
$systemBlocked = ($statusRow && $statusRow['system_status'] === 'blocked');
?>

<?php if ($systemBlocked): ?>
    <div style="
        background-color: #dc3545;
        color: white;
        text-align: center;
        padding: 10px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 5px;
        margin-bottom: 15px;">
        🔴 System is currently blocked by admin — transactions are disabled.
    </div>
    <style>
        button, input[type=submit] {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
        }
        form input, form select, form textarea {
            pointer-events: none !important;
            opacity: 0.7;
        }
    </style>
<?php endif; ?>
