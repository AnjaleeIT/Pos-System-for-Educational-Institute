<?php
require '../config.php';
require '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') { header("Location: /pos/index.php"); exit; }
require '../includes/header.php';

// totals
$totalPayments = $pdo->query("SELECT SUM(amount_paid) FROM payments")->fetchColumn();
$today = date('Y-m-d');
$todayPayments = $pdo->prepare("SELECT SUM(amount_paid) FROM payments WHERE DATE(payment_date)=?");
$todayPayments->execute([$today]);
$todaySum = $todayPayments->fetchColumn();

// external
$totalExternal = $pdo->query("SELECT SUM(amount) FROM external_payments")->fetchColumn();

?>
<div class="card">
  <h3>Finance Overview</h3>
  <p><strong>Total collected (internal):</strong> <?=number_format($totalPayments ?? 0,2)?></p>
  <p><strong>Collected today (<?=htmlspecialchars($today)?>):</strong> <?=number_format($todaySum ?? 0,2)?></p>
  <p><strong>Total external payments:</strong> <?=number_format($totalExternal ?? 0,2)?></p>
</div>
<?php require '../includes/footer.php'; ?>
