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
<script>
  (function() {
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', savedTheme);
  })();
</script>
<body class="admin-body">
<aside class="admin-sidebar" id="admin-sidebar">
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

<div class="admin-sidebar-overlay" id="sidebar-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:none"></div>

<main class="admin-main">
  <div class="admin-header">
    <button class="admin-burger" id="admin-burger" aria-label="Menu">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <h1>Gestion des commandes</h1>
    <button class="theme-toggle" id="theme-toggle" style="background:var(--white);border:1px solid var(--border);border-radius:10px;padding:.5rem">
      <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      <svg class="sun-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    </button>
  </div>
  <?php if($msg): ?>
  <div class="alert alert--<?= $msgType==='error'?'error':'success' ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="admin-table-wrapper">
    <table class="admin-table">
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
        <td style="text-align:right">
          <form method="POST" style="display:flex;gap:.5rem;align-items:center;justify-content:flex-end">
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
<script>
const burger = document.getElementById('admin-burger');
const sidebar = document.getElementById('admin-sidebar');
const overlay = document.getElementById('sidebar-overlay');
function toggleSidebar() { sidebar.classList.toggle('open'); overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none'; }
burger.addEventListener('click', toggleSidebar);
overlay.addEventListener('click', toggleSidebar);

// Theme Toggle
document.getElementById('theme-toggle').addEventListener('click', () => {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
});
</script>
</body></html>
