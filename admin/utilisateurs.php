<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
requireAdmin();
$db = getDB();
$users = $db->query("SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Utilisateurs Admin — Vitanova</title>
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
.admin-table td{padding:.75rem 1rem;border-bottom:1px solid var(--clr-border)}
.admin-table tr:hover td{background:var(--clr-surface)}
</style>
</head><body>
<aside class="admin-sidebar">
  <div class="logo">Vita<span>nova</span> Admin</div>
  <nav class="admin-nav" style="margin-top:1rem;flex:1">
    <a href="index.php">📊 Tableau de bord</a><a href="produits.php">📦 Produits</a>
    <a href="commandes.php">🛒 Commandes</a><a href="utilisateurs.php" class="active">👥 Utilisateurs</a>
    <a href="messages.php">✉️ Messages</a>
  </nav>
  <div style="padding:1.5rem;border-top:1px solid rgba(255,255,255,.15)"><a href="../" style="color:rgba(255,255,255,.7);font-size:.85rem">← Voir le site</a></div>
</aside>
<main class="admin-main">
  <h1 style="font-size:1.5rem;margin-bottom:1.5rem">Utilisateurs (<?= count($users) ?>)</h1>
  <div class="admin-table"><table>
    <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscription</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
    <tr>
      <td style="color:var(--clr-muted)">#<?= $u['id'] ?></td>
      <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
      <td style="color:var(--clr-muted)"><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="badge <?= $u['role']==='admin'?'badge--green':'badge--cat' ?>"><?= $u['role']==='admin'?'Admin':'Client' ?></span></td>
      <td style="color:var(--clr-muted);font-size:.82rem"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</main>
</body></html>
