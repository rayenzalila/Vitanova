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
<section class="section" style="background:var(--clr-surface);min-height:90vh;display:flex;align-items:center;padding:2rem 0">
  <div class="container" style="max-width:720px">
    <div id="confirmation-card" class="glass-card anim-pop-in" style="text-align:center">
      <div class="anim-pop-in" style="margin-bottom:1rem">
        <img src="mascots/Order-Sent-confirmation.png" alt="Success Mascot" style="height:280px;width:auto;display:block;margin:0 auto">
      </div>
      <div style="width:90px;height:90px;background:var(--green-pale);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 2rem">
        <svg width="48" height="48" fill="none" stroke="var(--clr-primary)" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      
      <h1 style="color:var(--clr-primary);margin-bottom:.75rem;font-size:clamp(1.75rem, 5vw, 2.5rem)">Commande confirmée !</h1>
      <p style="font-size:1.1rem;margin-bottom:1.5rem;color:var(--muted)">Merci, <strong><?= htmlspecialchars((string)($order['customer_name'] ?? '')) ?></strong> ! Nous vous contacterons pour confirmer la livraison.</p>
      
      <div style="background:var(--clr-primary);color:#fff;border-radius:var(--radius);padding:.75rem 1.5rem;display:inline-block;margin-bottom:2rem;font-weight:600">
        Commande n° #<?= str_pad((string)($order['id'] ?? '0'), 5, '0', STR_PAD_LEFT) ?>
      </div>

      <div class="confirmation-section" style="border-radius:var(--radius-lg);padding:1.75rem;text-align:left;margin-bottom:1.5rem">

        <h3 style="margin-bottom:1.25rem;font-size:.85rem;color:var(--clr-muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700">Détail de la commande</h3>
        <?php foreach ($items as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid rgba(0,0,0,0.05);font-size:.95rem">
          <span><?= htmlspecialchars((string)($item['name'] ?? 'Produit')) ?> × <?= (int)($item['quantity'] ?? 1) ?></span>
          <span style="font-weight:700;color:var(--clr-primary)"><?= formatPrice((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1)) ?></span>
        </div>
        <?php endforeach; ?>
        
        <div style="display:flex;justify-content:space-between;padding:1rem 0 0;font-weight:800;font-size:1.25rem;color:var(--clr-primary)">
          <span>Total</span><span><?= formatPrice((float)($order['total'] ?? 0)) ?></span>
        </div>
      </div>

      <div class="confirmation-section" style="border-radius:var(--radius-lg);padding:1.25rem 1.75rem;text-align:left;margin-bottom:2.5rem;font-size:.95rem">

        <div style="font-weight:700;color:var(--clr-text);margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Adresse de livraison
        </div>
        <p style="color:var(--clr-muted);margin:0">
          <?= htmlspecialchars((string)($order['customer_name'] ?? '')) ?><br>
          <?= htmlspecialchars((string)($order['address'] ?? '')) ?>, <?= htmlspecialchars((string)($order['postal_code'] ?? '')) ?> <?= htmlspecialchars((string)($order['city'] ?? '')) ?>
        </p>
      </div>

      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="boutique.php" class="btn btn-primary btn-lg btn-nav-exit">Retour à la boutique</a>
        <?php if (isLoggedIn()): ?>
          <a href="compte.php" class="btn btn-outline btn-lg btn-nav-exit">Mes commandes</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  localStorage.removeItem('vitanova_cart');
  document.querySelectorAll('.cart-badge').forEach(b => { b.textContent = '0'; b.style.display = 'none'; });

  // Signal the Admin Tab to refresh live
  const bc = new BroadcastChannel('vitanova_live_sync');
  bc.postMessage('refresh_analytics');


  const card = document.getElementById('confirmation-card');
  const exitBtns = document.querySelectorAll('.btn-nav-exit');

  exitBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const href = btn.getAttribute('href');
      
      card.classList.remove('anim-pop-in');
      card.classList.add('anim-pop-out');
      
      setTimeout(() => {
        window.location.href = href;
      }, 400);
    });
  });
});
</script>
<?php require_once 'includes/footer.php'; ?>
