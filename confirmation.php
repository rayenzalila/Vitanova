<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/messages.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) { header('Location: 404.php'); exit; }
    $items = json_decode($order['items'], true) ?? [];
} catch (Exception $e) { header('Location: index.php'); exit; }

$pageTitle = 'Commande confirmée — Vitanova';
require_once 'includes/header.php';
?>
<div style="margin-top:var(--nav-h)"></div>
<section class="section" style="background:var(--clr-surface);min-height:70vh;display:flex;align-items:center">
  <div class="container" style="text-align:center;max-width:680px;margin:0 auto">
    <div style="width:90px;height:90px;background:var(--clr-success-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 2rem" class="anim-fade-up">
      <svg width="48" height="48" fill="none" stroke="var(--clr-primary)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h1 class="anim-fade-up anim-delay-1" style="color:var(--clr-primary);margin-bottom:.75rem">Commande confirmée !</h1>
    <p class="anim-fade-up anim-delay-2">Merci, <strong><?= htmlspecialchars((string)($order['customer_name'] ?? '')) ?></strong> ! Nous vous contacterons pour confirmer la livraison.</p>
    <div style="background:var(--clr-primary);color:#fff;border-radius:var(--radius-md);padding:.75rem 1.5rem;display:inline-block;margin:1.5rem 0" class="anim-fade-up anim-delay-2">
      Commande n° <strong>#<?= str_pad((string)($order['id'] ?? '0'), 5, '0', STR_PAD_LEFT) ?></strong>
    </div>
    <div style="background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.75rem;text-align:left;margin-bottom:1.5rem" class="anim-fade-up anim-delay-3">
      <h3 style="margin-bottom:1rem;font-size:.9rem;color:var(--clr-muted);text-transform:uppercase;letter-spacing:.05em">Détail de la commande</h3>
      <?php foreach ($items as $item): ?>
      <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--clr-border);font-size:.9rem">
        <span><?= htmlspecialchars((string)($item['name'] ?? 'Produit')) ?> × <?= (int)($item['quantity'] ?? 1) ?></span>
        <span style="font-weight:700;color:var(--clr-primary)"><?= formatPrice((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1)) ?></span>
      </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;padding:.5rem 0;font-size:.9rem;color:var(--clr-muted)">
        <span>Livraison</span><span><?= (((float)($order['total'] ?? 0) - (float)($order['subtotal'] ?? 0)) > 0) ? formatPrice((float)($order['total'] ?? 0) - (float)($order['subtotal'] ?? 0)) : 'Gratuite' ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:.75rem 0 0;font-weight:800;font-size:1.1rem;color:var(--clr-primary)">
        <span>Total</span><span><?= formatPrice((float)($order['total'] ?? 0)) ?></span>
      </div>
    </div>
    <div style="background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.25rem 1.75rem;text-align:left;margin-bottom:2rem;font-size:.9rem;color:var(--clr-muted)" class="anim-fade-up anim-delay-3">
      <div style="font-weight:600;color:var(--clr-text);margin-bottom:.5rem">📍 Adresse de livraison</div>
      <?= htmlspecialchars((string)($order['customer_name'] ?? '')) ?><br>
      <?= htmlspecialchars((string)($order['address'] ?? '')) ?>, <?= htmlspecialchars((string)($order['postal_code'] ?? '')) ?> <?= htmlspecialchars((string)($order['city'] ?? '')) ?>
    </div>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap" class="anim-fade-up anim-delay-4">
      <a href="boutique.php" class="btn btn-primary btn-lg">Retour à la boutique</a>
      <?php if (isLoggedIn()): ?><a href="compte.php" class="btn btn-outline btn-lg">Mes commandes</a><?php endif; ?>
    </div>
  </div>
</section>
<script>localStorage.removeItem('vitanova_cart');document.querySelectorAll('.cart-badge').forEach(b=>{b.textContent='0';b.style.display='none';});</script>
<?php require_once 'includes/footer.php'; ?>
