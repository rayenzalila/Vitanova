<?php
require_once 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: boutique.php'); exit; }

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) { header('Location: 404.php'); exit; }

    // Avis
    $reviews = $db->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
    $reviews->execute([$id]);
    $reviews = $reviews->fetchAll();
    $avgRating = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;

    // Produits liés
    $related = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? ORDER BY RAND() LIMIT 3");
    $related->execute([$p['category'], $id]);
    $related = $related->fetchAll();

    // A-t-il déjà soumis un avis ?
    $hasReviewed = false;
    if (isLoggedIn()) {
        $chk = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        $chk->execute([$id, $_SESSION['user_id']]);
        $hasReviewed = (bool)$chk->fetch();
    }
} catch (Exception $e) { header('Location: boutique.php'); exit; }

$pageTitle = htmlspecialchars($p['name']) . ' — Vitanova';
$pageDesc  = htmlspecialchars($p['short_description']);
?>

<div style="margin-top:var(--nav-h)"></div>

<section class="section" style="background:#fff">
  <div class="container">
    <!-- Fil d'Ariane -->
    <nav style="font-size:.85rem;color:var(--clr-muted);margin-bottom:2rem" aria-label="Fil d'Ariane">
      <a href="/" style="color:var(--clr-primary)">Accueil</a> / 
      <a href="boutique.php" style="color:var(--clr-primary)">Boutique</a> / 
      <?= htmlspecialchars($p['name']) ?>
    </nav>

    <!-- Produit principal -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start">
      <!-- Image -->
      <div style="position:sticky;top:calc(var(--nav-h) + 1rem)">
        <div style="border-radius:var(--radius-lg);overflow:hidden;background:var(--clr-surface);border:1px solid var(--clr-border)">
          <?= productSvgPlaceholder($p['name'], $p['category']) ?>
        </div>
      </div>

      <!-- Infos -->
      <div>
        <span class="badge badge--cat" style="margin-bottom:1rem"><?= categoryLabel($p['category']) ?></span>
        <h1 style="font-size:2rem;margin-bottom:.75rem"><?= htmlspecialchars($p['name']) ?></h1>

        <?php if ($avgRating > 0): ?>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
          <?= renderStars($avgRating) ?>
          <span style="font-size:.85rem;color:var(--clr-muted)"><?= number_format($avgRating,1,',','') ?>/5 (<?= count($reviews) ?> avis)</span>
        </div>
        <?php endif; ?>

        <p style="font-size:1.05rem;color:var(--clr-muted);margin-bottom:1.5rem"><?= htmlspecialchars($p['short_description']) ?></p>
        <div style="font-size:2.25rem;font-weight:800;color:var(--clr-primary);margin-bottom:2rem"><?= formatPrice($p['price']) ?></div>

        <!-- Quantité -->
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
          <span style="font-weight:600;font-size:.9rem">Quantité :</span>
          <div style="display:flex;align-items:center;border:1.5px solid var(--clr-border);border-radius:var(--radius-md);overflow:hidden">
            <button id="qty-minus" style="padding:.6rem 1rem;border:none;background:var(--clr-surface);cursor:pointer;font-size:1.1rem;color:var(--clr-primary)" aria-label="Diminuer">−</button>
            <input id="qty-input" type="number" value="1" min="1" max="<?= $p['stock'] ?>" style="width:50px;text-align:center;border:none;font-weight:600;font-size:1rem;outline:none;padding:.6rem 0" aria-label="Quantité">
            <button id="qty-plus" style="padding:.6rem 1rem;border:none;background:var(--clr-surface);cursor:pointer;font-size:1.1rem;color:var(--clr-primary)" aria-label="Augmenter">+</button>
          </div>
          <span style="font-size:.85rem;color:var(--clr-muted)">Stock : <?= $p['stock'] ?></span>
        </div>

        <?php if ($p['stock'] > 0): ?>
        <button class="btn btn-primary btn-lg btn-block" id="add-to-cart-btn" data-add-to-cart
          data-product-id="<?= $p['id'] ?>"
          data-product-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
          data-product-price="<?= $p['price'] ?>"
          data-product-category="<?= $p['category'] ?>"
          data-product-slug="<?= $p['slug'] ?>"
          data-product-stock="<?= $p['stock'] ?>"
          style="margin-bottom:1rem">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          Ajouter au panier
        </button>
        <?php else: ?>
        <div class="alert alert--error">Ce produit est actuellement en rupture de stock.</div>
        <?php endif; ?>

        <div style="display:flex;gap:1.5rem;font-size:.82rem;color:var(--clr-muted);margin-top:1rem">
          <span>✅ Paiement à la livraison</span>
          <span>🚚 Livraison rapide</span>
          <span>🌿 100% Bio</span>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs" style="margin-top:4rem">
      <div style="display:flex;gap:0;border-bottom:2px solid var(--clr-border);margin-bottom:2rem">
        <?php foreach(['Description','Ingrédients','Avis clients (' . count($reviews) . ')'] as $i => $tab): ?>
        <button class="tab-btn" style="padding:.75rem 1.5rem;border:none;background:none;font-weight:600;font-size:.95rem;color:var(--clr-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;<?= $i===0 ? 'color:var(--clr-primary);border-bottom-color:var(--clr-primary)' : '' ?>"
          onmouseover="this.style.color='var(--clr-primary)'" onmouseout="if(!this.classList.contains('active'))this.style.color='var(--clr-muted)'">
          <?= $tab ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Tab 1: Description -->
      <div class="tab-content" style="line-height:1.9;color:var(--clr-muted)">
        <?= nl2br(htmlspecialchars($p['description'])) ?>
        <?php if ($p['benefits']): ?>
        <h4 style="color:var(--clr-text);margin-top:2rem;margin-bottom:1rem">✅ Bienfaits clés</h4>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:.5rem">
          <?php foreach(explode("\n", $p['benefits']) as $b): if(trim($b)): ?>
          <li style="display:flex;align-items:flex-start;gap:.5rem">
            <svg width="18" height="18" fill="none" stroke="#0f6e56" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars(trim($b)) ?>
          </li>
          <?php endif; endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>

      <!-- Tab 2: Ingrédients -->
      <div class="tab-content">
        <p style="line-height:1.9;color:var(--clr-muted)"><?= nl2br(htmlspecialchars($p['ingredients'])) ?></p>
      </div>

      <!-- Tab 3: Avis -->
      <div class="tab-content">
        <?php if (empty($reviews)): ?>
        <p style="color:var(--clr-muted);font-style:italic">Soyez le premier à donner votre avis sur ce produit !</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:1.25rem;margin-bottom:2.5rem">
          <?php foreach ($reviews as $r): ?>
          <div class="card" style="padding:1.25rem">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
              <div>
                <span style="font-weight:600"><?= htmlspecialchars($r['user_name']) ?></span>
                <div><?= renderStars($r['rating']) ?></div>
              </div>
              <span style="font-size:.8rem;color:var(--clr-muted)"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
            </div>
            <p style="font-size:.9rem;color:var(--clr-muted)"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire avis -->
        <?php if (isLoggedIn() && !$hasReviewed): ?>
        <div class="card">
          <h3 style="margin-bottom:1.25rem">Laisser un avis</h3>
          <form id="review-form" data-validate-form>
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <?= csrfField() ?>
            <div class="form-group">
              <label class="form-label">Note *</label>
              <?= renderStars(0, true, 'rating') ?>
              <span id="rating-error"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="review-comment">Commentaire *</label>
              <textarea id="review-comment" name="comment" rows="4" class="form-control" placeholder="Partagez votre expérience..."
                data-validate="required|min:10"
                data-msg-required="Veuillez rédiger un commentaire."
                data-msg-min="Votre avis est trop court. Veuillez écrire au moins 10 caractères."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" id="submit-review">Publier mon avis</button>
          </form>
        </div>
        <?php elseif (!isLoggedIn()): ?>
        <div class="alert alert--error">
          Vous devez être connecté pour laisser un avis. <a href="compte.php" style="color:inherit;font-weight:700">Se connecter</a>
        </div>
        <?php elseif ($hasReviewed): ?>
        <div class="alert alert--success">Vous avez déjà soumis un avis pour ce produit. Merci !</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Produits liés -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:5rem">
      <h2 style="margin-bottom:2rem">Vous pourriez aussi aimer</h2>
      <div class="products-grid">
        <?php foreach ($related as $r): ?>
        <article class="product-card">
          <a href="produit.php?id=<?= $r['id'] ?>" class="product-card__img" style="display:block">
            <?= productSvgPlaceholder($r['name'], $r['category']) ?>
          </a>
          <div class="product-card__body">
            <h3 class="product-card__name"><a href="produit.php?id=<?= $r['id'] ?>" style="color:inherit"><?= htmlspecialchars($r['name']) ?></a></h3>
            <p class="product-card__desc"><?= htmlspecialchars($r['short_description']) ?></p>
            <div class="product-card__footer">
              <span class="product-card__price"><?= formatPrice($r['price']) ?></span>
              <a href="produit.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm">Voir</a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<style>
.star-icon polygon { transition: fill 0.3s ease; }
.star-label { cursor: pointer; transition: transform 0.15s; }
.star-label:hover { transform: scale(1.25); }
</style>

<script>
// Interactive Stars
document.querySelectorAll('.stars[data-interactive="true"]').forEach(starsContainer => {
  const labels = Array.from(starsContainer.querySelectorAll('.star-label'));
  const updateStars = (hoverIndex = -1) => {
    let selectedIndex = -1;
    labels.forEach((l, i) => { if (l.querySelector('input').checked) selectedIndex = i; });
    const activeIndex = hoverIndex !== -1 ? hoverIndex : selectedIndex;
    labels.forEach((l, i) => {
      const poly = l.querySelector('polygon');
      if (poly) poly.setAttribute('fill', i <= activeIndex ? '#f59e0b' : '#e5e7eb');
    });
  };
  labels.forEach((label, index) => {
    label.addEventListener('mouseenter', () => updateStars(index));
    label.querySelector('input').addEventListener('change', () => updateStars());
  });
  starsContainer.addEventListener('mouseleave', () => updateStars());
  updateStars();
});

// Quantity selector
const qtyInput = document.getElementById('qty-input');
document.getElementById('qty-minus')?.addEventListener('click', () => { if (qtyInput.value > 1) qtyInput.value--; });
document.getElementById('qty-plus')?.addEventListener('click', () => { if (qtyInput.value < parseInt(qtyInput.max)) qtyInput.value++; });

// Review form AJAX
document.getElementById('review-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const rating = form.querySelector('input[name="rating"]:checked')?.value;
  if (!rating) { document.getElementById('rating-error').textContent = 'Veuillez sélectionner une note.'; return; }
  if (!window.VitanovaValidation?.validateForm(form)) return;
  const btn = document.getElementById('submit-review');
  btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Publication...';
  const fd = new FormData(form);
  try {
    const res = await fetch('api/reviews.php', { method: 'POST', body: fd });
    const data = await res.json();
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1500);
  } catch { showToast('Une erreur inattendue est survenue.', 'error'); }
  btn.disabled = false; btn.textContent = 'Publier mon avis';
});
</script>

<?php require_once 'includes/footer.php'; ?>
