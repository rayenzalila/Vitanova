<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
requireAdmin();
$db = getDB();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowed = ['en_attente','confirmee','expediee','livree'];
    if (in_array($_POST['status'], $allowed)) {
        try {
            $stmt = $db->prepare("UPDATE orders SET status=? WHERE id=?");
            $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
            $msg = MSG_ADMIN_ORDER_STATUS_UPDATED;
        } catch(Exception $e) { $msg = MSG_GENERIC_SERVER_ERROR; $msgType = 'error'; }
    }
}

$orders = $db->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Commandes Admin — Vitanova</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body{display:flex;min-height:100vh;background:var(--clr-surface)}
.admin-sidebar{width:240px;background:var(--clr-primary);color:#fff;flex-shrink:0;padding:2rem 0;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.admin-sidebar .logo{padding:0 1.5rem 2rem;font-size:1.25rem;font-weight:800;border-bottom:1px solid rgba(255,255,255,.15)}
.admin-sidebar .logo span{color:var(--clr-accent)}
.admin-nav a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.5rem;color:rgba(255,255,255,.8);font-size:.9rem;font-weight:500;transition:all .2s;border-left:3px solid transparent}
.admin-nav a:hover,.admin-nav a.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--clr-accent)}
.admin-main{flex:1;padding:2rem;overflow-x:hidden}
.admin-table{background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-lg);overflow:auto;margin-top:1.5rem}
.admin-table table{width:100%;border-collapse:collapse;font-size:.875rem}
.admin-table th{background:var(--clr-surface);padding:.875rem 1rem;text-align:left;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--clr-muted);border-bottom:2px solid var(--clr-border)}
.admin-table td{padding:.75rem 1rem;border-bottom:1px solid var(--clr-border);vertical-align:top}
.admin-table tr:hover td{background:var(--clr-surface)}
select.status-sel{border:1.5px solid var(--clr-border);border-radius:6px;padding:.3rem .6rem;font-size:.82rem;font-family:inherit;cursor:pointer;background:#fff}
</style>
</head>
<body>
<aside class="admin-sidebar">
  <div class="logo">Vita<span>nova</span> Admin</div>
  <nav class="admin-nav" style="margin-top:1rem;flex:1">
    <a href="index.php">📊 Tableau de bord</a>
    <a href="produits.php">📦 Produits</a>
    <a href="commandes.php" class="active">🛒 Commandes</a>
    <a href="utilisateurs.php">👥 Utilisateurs</a>
    <a href="messages.php">✉️ Messages</a>
  </nav>
  <div style="padding:1.5rem;border-top:1px solid rgba(255,255,255,.15)">
    <a href="../" style="color:rgba(255,255,255,.7);font-size:.85rem">← Voir le site</a>
  </div>
</aside>

<main class="admin-main">
  <h1 style="font-size:1.5rem;margin-bottom:1.5rem">Gestion des commandes</h1>
  <?php if($msg): ?>
  <div class="alert alert--<?= $msgType==='error'?'error':'success' ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="admin-table">
    <table>
      <thead><tr><th>N°</th><th>Client</th><th>Articles</th><th>Total</th><th>Statut</th><th>Date</th><th>Modifier statut</th></tr></thead>
      <tbody>
      <?php if(empty($orders)): ?>
      <tr><td colspan="7" style="text-align:center;color:var(--clr-muted);padding:2rem">Aucune commande.</td></tr>
      <?php else: foreach($orders as $o):
        $items = json_decode($o['items'], true) ?? [];
      ?>
      <tr>
        <td><strong>#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
        <td>
          <div style="font-weight:600"><?= htmlspecialchars($o['customer_name']) ?></div>
          <div style="font-size:.78rem;color:var(--clr-muted)"><?= htmlspecialchars($o['email']) ?></div>
          <div style="font-size:.78rem;color:var(--clr-muted)"><?= htmlspecialchars($o['phone']) ?></div>
        </td>
        <td style="font-size:.82rem;color:var(--clr-muted)">
          <?= implode('<br>', array_map(fn($i) => htmlspecialchars($i['name']) . ' × ' . $i['quantity'], $items)) ?>
        </td>
        <td style="font-weight:700;color:var(--clr-primary)"><?= formatPrice($o['total']) ?></td>
        <td><?= orderStatusBadge($o['status']) ?></td>
        <td style="color:var(--clr-muted);font-size:.82rem;white-space:nowrap"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
        <td>
          <form method="POST" style="display:flex;gap:.5rem;align-items:center">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <select name="status" class="status-sel">
              <?php foreach(['en_attente'=>'En attente','confirmee'=>'Confirmée','expediee'=>'Expédiée','livree'=>'Livrée'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $o['status']===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">OK</button>
          </form>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
