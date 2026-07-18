<?php
require '../config.php';
require '../includes/auth.php';
if ($_SESSION['user']['role'] !== 'admin') { header("Location: /pos/index.php"); exit; }
require '../includes/header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $name = trim($_POST['name']);
    $monthly_fee = floatval($_POST['monthly_fee']);
    $total_fee = floatval($_POST['total_fee']);

    // check if exists
    $s = $pdo->prepare("SELECT id FROM fees WHERE type=? AND name=? LIMIT 1");
    $s->execute([$type,$name]);
    if ($s->fetch()) {
        $u = $pdo->prepare("UPDATE fees SET monthly_fee=?, total_fee=? WHERE type=? AND name=?");
        $u->execute([$monthly_fee, $total_fee, $type, $name]);
        $msg = "Updated fee.";
    } else {
        $i = $pdo->prepare("INSERT INTO fees (type, name, monthly_fee, total_fee) VALUES (?, ?, ?, ?)");
        $i->execute([$type,$name,$monthly_fee,$total_fee]);
        $msg = "Added fee.";
    }
}

// fetch all fees
$fees = $pdo->query("SELECT * FROM fees ORDER BY type, name")->fetchAll();
?>
<div class="card">
  <h3>Manage Fees</h3>
  <?php if($msg): ?><div class="success"><?=htmlspecialchars($msg)?></div><?php endif; ?>

  <form method="post" style="margin-bottom:20px;">
    <div class="form-row">
      <label>Type</label>
      <select name="type">
        <option value="course">Course</option>
        <option value="school">School</option>
      </select>
    </div>
    <div class="form-row">
      <label>Name (course/grade)</label>
      <input type="text" name="name" required />
    </div>
    <div class="form-row">
      <label>Monthly Fee</label>
      <input type="number" name="monthly_fee" step="0.01" value="0" />
    </div>
    <div class="form-row">
      <label>Total Fee</label>
      <input type="number" name="total_fee" step="0.01" value="0" />
    </div>
    <div class="form-row">
      <label></label>
      <button class="btn" type="submit">Save</button>
    </div>
  </form>

  <h4>Existing Fees</h4>
  <table class="table">
    <thead><tr><th>Type</th><th>Name</th><th>Monthly</th><th>Total</th></tr></thead>
    <tbody>
      <?php foreach($fees as $f): ?>
      <tr>
        <td><?=htmlspecialchars($f['type'])?></td>
        <td><?=htmlspecialchars($f['name'])?></td>
        <td><?=number_format($f['monthly_fee'],2)?></td>
        <td><?=number_format($f['total_fee'],2)?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require '../includes/footer.php'; ?>
