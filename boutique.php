<?php
$pageTitle = 'Boutique — Vitanova';
$pageDesc  = 'Découvrez tous nos compléments alimentaires bio : stress, sommeil, énergie et bien-être. Filtrez par catégorie et trouvez votre formule.';
require_once 'includes/header.php';

// Filtres & tri
$cat  = in_array($_GET['cat'] ?? '', ['stress','sommeil','energie','bien-etre']) ? $_GET['cat'] : '';
$sort = in_array($_GET['sort'] ?? '', ['price_asc','price_desc','newest']) ? $_GET['sort'] : 'newest';
$q    = trim(strip_tags($_GET['q'] ?? ''));

// Requête DB
try {
    $db = getDB();
    $where = ['1=1'];
    $params = [];
    if ($cat)  { $where[] = 'category = ?'; $params[] = $cat; }
    if ($q)    { $where[] = '(name LIKE ? OR short_description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
    $orderMap = ['price_asc'=>'price ASC','price_desc'=>'price DESC','newest'=>'created_at DESC'];
    $order = $orderMap[$sort];
    $sql  = 'SELECT * FROM products WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) { $products = []; }

$categories = [
    '' => 'Toutes les catégories',
    'stress' => 'Stress & Anxiété',
    'sommeil' => 'Sommeil',
    'energie' => 'Énergie & Focus',
    'bien-etre' => 'Bien-être',
];
?>

<div class="page-header">
  <div class="container">
    <span class="badge badge--cat" style="margin-bottom:.75rem">🌿 Notre Collection</span>
    <h1>Boutique Vitanova</h1>
    <p style="margin-top:.5rem;color:var(--clr-muted)">Des formules naturelles, conçues avec soin pour votre bien-être.</p>
  </div>
</div>

<section class="section-sm" style="background:#fff">
  <div class="container">

    <!-- Filtres -->
    <form method="GET" id="filter-form" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;margin-bottom:2.5rem;padding:1.25rem;background:var(--clr-surface);border-radius:var(--radius-lg);border:1px solid var(--clr-border)">
      <div style="flex:1;min-width:200px">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un produit..." class="form-control" style="margin:0">
      </div>
      <select name="cat" class="form-control" style="max-width:200px;margin:0" onchange="this.form.submit()">
        <?php foreach ($categories as $val => $label): ?>
        <option value="<?= $val ?>" <?= $cat === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
      <select name="sort" class="form-control" style="max-width:180px;margin:0" onchange="this.form.submit()">
        <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>Plus récents</option>
        <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>Prix croissant</option>
        <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>Prix décroissant</option>
      </select>
      <button type="submit" class="btn btn-primary">Filtrer</button>
      <?php if ($cat || $q): ?>
      <a href="boutique.php" class="btn btn-ghost">Réinitialiser</a>
      <?php endif; ?>
    </form>

    <!-- Résultats -->
    <p style="color:var(--clr-muted);font-size:.9rem;margin-bottom:1.5rem">
      <?= count($products) ?> produit<?= count($products) !== 1 ? 's' : '' ?> trouvé<?= count($products) !== 1 ? 's' : '' ?>
      <?php if ($cat): ?> dans <strong><?= htmlspecialchars($categories[$cat]) ?></strong><?php endif; ?>
    </p>

    <?php if (empty($products)): ?>
    <div style="text-align:center;padding:5rem 1rem">
      <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
      <h3 style="margin-bottom:.5rem;color:var(--clr-muted)">Aucun produit ne correspond à votre recherche.</h3>
      <p style="margin-bottom:1.5rem">Essayez une autre catégorie ou réinitialisez les filtres.</p>
      <a href="boutique.php" class="btn btn-primary">Voir tous les produits</a>
    </div>
    <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $i => $p): ?>
      <article class="product-card reveal reveal-delay-<?= ($i % 3) + 1 ?>">
        <a href="produit.php?id=<?= $p['id'] ?>" class="product-card__img" style="display:block">
          <?= productSvgPlaceholder($p['name'], $p['category']) ?>
        </a>
        <div class="product-card__body">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
            <span class="badge badge--cat"><?= categoryLabel($p['category']) ?></span>
            <?php if ($p['stock'] < 15): ?>
            <span style="font-size:.72rem;color:var(--clr-error);font-weight:600">⚠ Stock limité</span>
            <?php endif; ?>
          </div>
          <h3 class="product-card__name">
            <a href="produit.php?id=<?= $p['id'] ?>" style="color:inherit"><?= htmlspecialchars($p['name']) ?></a>
          </h3>
          <p class="product-card__desc"><?= htmlspecialchars($p['short_description']) ?></p>
          <div class="product-card__footer">
            <span class="product-card__price"><?= formatPrice($p['price']) ?></span>
            <?php if ($p['stock'] > 0): ?>
            <button class="btn btn-primary btn-sm" data-add-to-cart
              data-product-id="<?= $p['id'] ?>"
              data-product-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
              data-product-price="<?= $p['price'] ?>"
              data-product-category="<?= $p['category'] ?>"
              data-product-slug="<?= $p['slug'] ?>"
              data-product-stock="<?= $p['stock'] ?>">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Panier
            </button>
            <?php else: ?>
            <span style="font-size:.8rem;color:var(--clr-error);font-weight:600">Rupture de stock</span>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
