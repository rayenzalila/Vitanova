<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
requireAdmin();
$db = getDB();
$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Messages Admin — Vitanova</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body{display:flex;min-height:100vh;background:var(--clr-surface)}
.admin-sidebar{width:240px;background:var(--clr-primary);color:#fff;flex-shrink:0;padding:2rem 0;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.admin-sidebar .logo{padding:0 1.5rem 2rem;font-size:1.25rem;font-weight:800;border-bottom:1px solid rgba(255,255,255,.15)}
.admin-sidebar .logo span{color:var(--clr-accent)}
.admin-nav a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.5rem;color:rgba(255,255,255,.8);font-size:.9rem;font-weight:500;transition:all .2s;border-left:3px solid transparent}
.admin-nav a:hover,.admin-nav a.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--clr-accent)}
.admin-main{flex:1;padding:2rem;overflow-x:hidden;max-width:100%}
.msg-card{background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-md);margin-bottom:1rem;overflow:hidden}
.msg-card__header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;cursor:pointer;user-select:none;transition:background .2s}
.msg-card__header:hover{background:var(--clr-surface)}
.msg-card__body{padding:0 1.25rem;max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s ease}
.msg-card.open .msg-card__body{max-height:400px;padding:0 1.25rem 1.25rem}
.msg-card__toggle{color:var(--clr-primary);font-size:1.2rem;transition:transform .3s}
.msg-card.open .msg-card__toggle{transform:rotate(180deg)}
</style>
</head><body>
<aside class="admin-sidebar">
  <div class="logo">Vita<span>nova</span> Admin</div>
  <nav class="admin-nav" style="margin-top:1rem;flex:1">
    <a href="index.php">📊 Tableau de bord</a><a href="produits.php">📦 Produits</a>
    <a href="commandes.php">🛒 Commandes</a><a href="utilisateurs.php">👥 Utilisateurs</a>
    <a href="messages.php" class="active">✉️ Messages</a>
  </nav>
  <div style="padding:1.5rem;border-top:1px solid rgba(255,255,255,.15)"><a href="../" style="color:rgba(255,255,255,.7);font-size:.85rem">← Voir le site</a></div>
</aside>
<main class="admin-main">
  <h1 style="font-size:1.5rem;margin-bottom:1.5rem">Messages de contact (<?= count($messages) ?>)</h1>
  <?php if(empty($messages)): ?>
  <div class="card" style="text-align:center;color:var(--clr-muted);padding:3rem">Aucun message reçu.</div>
  <?php else: foreach($messages as $m): ?>
  <div class="msg-card" onclick="this.classList.toggle('open')">
    <div class="msg-card__header">
      <div>
        <strong><?= htmlspecialchars($m['name']) ?></strong>
        <span style="color:var(--clr-muted);margin-left:.75rem;font-size:.85rem"><?= htmlspecialchars($m['email']) ?></span>
        <div style="font-size:.9rem;font-weight:600;color:var(--clr-primary);margin-top:.2rem"><?= htmlspecialchars($m['subject']) ?></div>
      </div>
      <div style="display:flex;align-items:center;gap:1rem">
        <span style="font-size:.78rem;color:var(--clr-muted)"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></span>
        <span class="msg-card__toggle">▼</span>
      </div>
    </div>
    <div class="msg-card__body">
      <p style="white-space:pre-wrap;font-size:.9rem;color:var(--clr-muted);line-height:1.7"><?= htmlspecialchars($m['message']) ?></p>
      <div style="margin-top:1rem">
        <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= rawurlencode($m['subject']) ?>" class="btn btn-sm btn-primary">Répondre par email</a>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</main>
<script>document.querySelectorAll('.msg-card').forEach(c=>c.addEventListener('click',()=>c.classList.toggle('open')));</script>
</body></html>
