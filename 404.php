<?php
$pageTitle = '404 — Page introuvable — Vitanova';
require_once 'includes/header.php';
http_response_code(404);
?>
<div style="margin-top:var(--nav-h);min-height:70vh;display:flex;align-items:center;justify-content:center;background:var(--clr-surface)">
  <div style="text-align:center;padding:4rem 1rem">
    <div class="anim-fade-up" style="margin-bottom:1.5rem">
      <img src="mascots/error%20404.png" alt="Mascot" style="height:250px;width:auto;display:block;margin:0 auto">
    </div>
    <div style="font-size:8rem;font-weight:800;color:var(--clr-accent);line-height:1;margin-bottom:1rem" class="anim-fade-up">404</div>
    <h1 class="anim-fade-up anim-delay-1" style="font-size:1.75rem;margin-bottom:1rem">Page introuvable</h1>
    <p class="anim-fade-up anim-delay-2" style="color:var(--clr-muted);margin-bottom:2.5rem;max-width:420px;margin-left:auto;margin-right:auto"><?= MSG_404_TEXT ?></p>
    <div class="anim-fade-up anim-delay-3" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="/" class="btn btn-primary btn-lg"><?= MSG_404_BACK ?></a>
      <a href="boutique.php" class="btn btn-outline btn-lg">Voir la boutique</a>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
