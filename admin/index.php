<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
requireAdmin();

try {
    $db = getDB();
    $totalOrders  = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders= $db->query("SELECT COUNT(*) FROM orders WHERE status='en_attente'")->fetchColumn();
    $totalProducts= $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalUsers   = $db->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
    $recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch(Exception $e) { $totalOrders=$pendingOrders=$totalProducts=$totalUsers=0; $recentOrders=[]; }

$pageTitle = 'Tableau de bord Admin — Vitanova';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body{display:flex;min-height:100vh;background:var(--clr-surface)}
.admin-sidebar{width:240px;background:var(--clr-primary);color:#fff;flex-shrink:0;display:flex;flex-direction:column;padding:2rem 0;position:sticky;top:0;height:100vh;overflow-y:auto}
.admin-sidebar .logo{padding:0 1.5rem 2rem;font-size:1.25rem;font-weight:800;border-bottom:1px solid rgba(255,255,255,.15)}
.admin-sidebar .logo span{color:var(--clr-accent)}
.admin-nav a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.5rem;color:rgba(255,255,255,.8);font-size:.9rem;font-weight:500;transition:all .2s;border-left:3px solid transparent}
.admin-nav a:hover,.admin-nav a.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--clr-accent)}
.admin-main{flex:1;padding:2rem;overflow-x:hidden}
.admin-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
.admin-header h1{font-size:1.5rem}
.metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2.5rem}
.metric-card{background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.5rem}
.metric-card__value{font-size:2rem;font-weight:800;color:var(--clr-primary);margin-bottom:.25rem}
.metric-card__label{font-size:.85rem;color:var(--clr-muted)}
.metric-card__icon{font-size:1.75rem;margin-bottom:.75rem}
.admin-table{background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-lg);overflow:hidden}
.admin-table table{width:100%;border-collapse:collapse;font-size:.875rem}
.admin-table th{background:var(--clr-surface);padding:.875rem 1rem;text-align:left;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--clr-muted);border-bottom:2px solid var(--clr-border)}
.admin-table td{padding:.875rem 1rem;border-bottom:1px solid var(--clr-border);color:var(--clr-text)}
.admin-table tr:last-child td{border-bottom:none}
.admin-table tr:hover td{background:var(--clr-surface)}
.section-title-sm{font-size:1rem;font-weight:700;margin-bottom:1rem;color:var(--clr-text)}
</style>
</head>
<body>

<aside class="admin-sidebar">
  <div class="logo">Vita<span>nova</span> Admin</div>
  <nav class="admin-nav" style="margin-top:1rem;flex:1">
    <a href="index.php" class="active">📊 Tableau de bord</a>
    <a href="produits.php">📦 Produits</a>
    <a href="commandes.php">🛒 Commandes</a>
    <a href="utilisateurs.php">👥 Utilisateurs</a>
    <a href="messages.php">✉️ Messages</a>
  </nav>
  <div style="padding:1.5rem;border-top:1px solid rgba(255,255,255,.15)">
    <a href="../" style="color:rgba(255,255,255,.7);font-size:.85rem">← Voir le site</a><br>
    <a href="../compte.php?action=logout" style="color:rgba(255,255,255,.5);font-size:.8rem;margin-top:.5rem;display:block">Déconnexion</a>
  </div>
</aside>

<main class="admin-main">
  <div class="admin-header">
    <h1>Tableau de bord</h1>
    <span style="color:var(--clr-muted);font-size:.9rem">Bienvenue, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
  </div>

  <div class="metrics">
    <?php foreach([
      ['📦', $totalOrders,   'Total commandes',    ''],
      ['⏳', $pendingOrders, 'En attente',         'color:var(--clr-error)'],
      ['🌿', $totalProducts, 'Total produits',      ''],
      ['👥', $totalUsers,    'Clients enregistrés',''],
    ] as $m): ?>
    <div class="metric-card">
      <div class="metric-card__icon"><?= $m[0] ?></div>
      <div class="metric-card__value" style="<?= $m[3] ?>"><?= $m[1] ?></div>
      <div class="metric-card__label"><?= $m[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <p class="section-title-sm">Commandes récentes</p>
  <div class="admin-table">
    <table>
      <thead><tr>
        <th>N°</th><th>Client</th><th>Email</th><th>Total</th><th>Statut</th><th>Date</th><th>Détail</th>
      </tr></thead>
      <tbody>
      <?php if(empty($recentOrders)): ?>
      <tr><td colspan="7" style="text-align:center;color:var(--clr-muted);padding:2rem">Aucune commande.</td></tr>
      <?php else: foreach($recentOrders as $o): ?>
      <tr>
        <td><strong>#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
        <td><?= htmlspecialchars($o['customer_name']) ?></td>
        <td style="color:var(--clr-muted)"><?= htmlspecialchars($o['email']) ?></td>
        <td style="font-weight:700;color:var(--clr-primary)"><?= formatPrice($o['total']) ?></td>
        <td><?= orderStatusBadge($o['status']) ?></td>
        <td style="color:var(--clr-muted)"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
        <td><a href="commandes.php" style="color:var(--clr-primary);font-size:.82rem">Voir →</a></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
