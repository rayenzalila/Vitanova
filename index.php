<?php
$pageTitle = 'Vitanova — Compléments Naturels Bio | Bien-être & Santé';
$pageDesc  = 'Découvrez Vitanova, votre marque de compléments alimentaires naturels bio pour le stress, le sommeil, l\'énergie et le bien-être. Livraison en France.';
require_once 'includes/header.php';

// 3 produits phares aléatoires
try {
    $db = getDB();
    $featured = $db->query("SELECT * FROM products WHERE stock > 0 ORDER BY RAND() LIMIT 3")->fetchAll();
} catch (Exception $e) { $featured = []; }
?>

<!-- ── HERO ── -->
<section class="hero parallax-hero" style="min-height:100vh;display:flex;align-items:center;background:linear-gradient(135deg,#f7fdf9 0%,#e1f5ee 50%,#fff 100%);margin-top:var(--nav-h);position:relative;overflow:hidden;">
  <div class="botanical-pattern">
    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg"><circle cx="10%" cy="20%" r="180" fill="#0f6e56"/><circle cx="85%" cy="70%" r="240" fill="#5dcaa5"/><circle cx="60%" cy="10%" r="120" fill="#0f6e56"/><circle cx="5%" cy="80%" r="100" fill="#5dcaa5"/></svg>
  </div>
  <div class="parallax-bg" aria-hidden="true">
    <svg viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;opacity:.04">
      <path d="M400,300 Q450,200 500,300 Q450,400 400,300Z" fill="#0f6e56"/>
      <path d="M200,400 Q250,300 300,400 Q250,500 200,400Z" fill="#5dcaa5"/>
      <path d="M600,150 Q650,50 700,150 Q650,250 600,150Z" fill="#0f6e56"/>
    </svg>
  </div>
  <div class="container" style="position:relative;z-index:1;padding-top:4rem;padding-bottom:4rem;">
    <div style="max-width:650px">
      <span class="badge badge--cat anim-fade-up" style="margin-bottom:1.5rem;display:inline-flex">🌿 100% Naturel &amp; Bio</span>
      <h1 class="anim-fade-up anim-delay-1" style="font-size:clamp(2.2rem,6vw,4rem);line-height:1.1;margin-bottom:1.25rem">
        Retrouvez votre<br><span style="color:var(--clr-primary)">équilibre naturel</span>
      </h1>
      <p class="anim-fade-up anim-delay-2" style="font-size:1.15rem;color:var(--clr-muted);margin-bottom:2.5rem;max-width:520px;line-height:1.8">
        Des compléments alimentaires bio formulés avec les plantes les plus puissantes de la nature pour soutenir votre bien-être au quotidien.
      </p>
      <div class="anim-fade-up anim-delay-3" style="display:flex;gap:1rem;flex-wrap:wrap">
        <a href="boutique.php" class="btn btn-primary btn-lg">Découvrir nos produits</a>
        <a href="#bienfaits" class="btn btn-outline btn-lg">Nos bienfaits</a>
      </div>
      <div class="anim-fade-up anim-delay-4" style="display:flex;gap:2rem;margin-top:2.5rem;flex-wrap:wrap">
        <?php foreach([['2 000+','Clients satisfaits'],['100%','Ingrédients naturels'],['Certifié','Bio & Vegan']] as $stat): ?>
        <div>
          <div style="font-size:1.4rem;font-weight:800;color:var(--clr-primary)"><?= $stat[0] ?></div>
          <div style="font-size:.85rem;color:var(--clr-muted)"><?= $stat[1] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── BIENFAITS ── -->
<section class="section" id="bienfaits" style="background:#fff">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:3.5rem">
      <h2 class="section-title">Nos bienfaits naturels</h2>
      <p class="section-sub">Chaque formule est pensée pour un objectif précis, avec des actifs cliniquement reconnus.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem">
      <?php
      $bienfaits = [
        ['🧘','Stress & Anxiété','Retrouvez la sérénité avec nos adaptogènes naturels qui régulent le cortisol.','stress'],
        ['🌙','Sommeil','Endormissement rapide et sommeil réparateur grâce à nos plantes sédatives douces.','sommeil'],
        ['⚡','Énergie & Focus','Boost naturel d\'énergie et concentration optimale sans nervosité ni crash.','energie'],
        ['🌿','Bien-être','Soutien global de l\'organisme pour une vitalité rayonnante au quotidien.','bien-etre'],
      ];
      foreach ($bienfaits as $i => $b): ?>
      <a href="boutique.php?cat=<?= $b[3] ?>" class="card reveal reveal-delay-<?= $i+1 ?>" style="text-align:center;text-decoration:none;cursor:pointer">
        <div style="font-size:2.5rem;margin-bottom:1rem"><?= $b[0] ?></div>
        <h3 style="color:var(--clr-primary);margin-bottom:.5rem"><?= $b[1] ?></h3>
        <p style="font-size:.875rem"><?= $b[2] ?></p>
        <span style="display:inline-block;margin-top:1rem;font-size:.8rem;font-weight:600;color:var(--clr-accent)">Voir les produits →</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── PRODUITS PHARES ── -->
<?php if (!empty($featured)): ?>
<section class="section" style="background:var(--clr-surface)">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:3rem">
      <h2 class="section-title">Nos produits phares</h2>
      <p class="section-sub">Sélectionnés pour leur efficacité et leur qualité exceptionnelle.</p>
    </div>
    <div class="products-grid">
      <?php foreach ($featured as $i => $p): ?>
      <article class="product-card reveal reveal-delay-<?= $i+1 ?>">
        <a href="produit.php?id=<?= $p['id'] ?>" class="product-card__img" style="display:block">
          <?= productSvgPlaceholder($p['name'], $p['category']) ?>
        </a>
        <div class="product-card__body">
          <span class="badge badge--cat" style="margin-bottom:.5rem"><?= categoryLabel($p['category']) ?></span>
          <h3 class="product-card__name"><?= htmlspecialchars($p['name']) ?></h3>
          <p class="product-card__desc"><?= htmlspecialchars($p['short_description']) ?></p>
          <div class="product-card__footer">
            <span class="product-card__price"><?= formatPrice($p['price']) ?></span>
            <button class="btn btn-primary btn-sm" data-add-to-cart
              data-product-id="<?= $p['id'] ?>"
              data-product-name="<?= htmlspecialchars($p['name']) ?>"
              data-product-price="<?= $p['price'] ?>"
              data-product-category="<?= $p['category'] ?>"
              data-product-slug="<?= $p['slug'] ?>"
              data-product-stock="<?= $p['stock'] ?>">
              + Panier
            </button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="text-center reveal" style="margin-top:3rem">
      <a href="boutique.php" class="btn btn-outline btn-lg">Voir tous nos produits</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── TÉMOIGNAGES ── -->
<section class="section" style="background:#fff">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:3rem">
      <h2 class="section-title">Ce que disent nos clients</h2>
      <p class="section-sub">Des milliers de personnes font confiance à Vitanova chaque jour.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem">
      <?php
      $testimonials = [
        ['Marie L.','Paris','Sérénia Zen','Après 3 semaines, mon niveau de stress a vraiment diminué. Je me sens beaucoup plus calme au travail. Je recommande vivement !','⭐⭐⭐⭐⭐'],
        ['Thomas B.','Lyon','Noctalis Sommeil','Enfin un produit qui fonctionne vraiment pour le sommeil ! Je m\'endors en 20 minutes et je me réveille reposé.','⭐⭐⭐⭐⭐'],
        ['Sophie M.','Bordeaux','Vitalys Énergie','Mon énergie a décollé sans les effets secondaires des boissons énergisantes. Naturel et efficace !','⭐⭐⭐⭐⭐'],
      ];
      foreach ($testimonials as $i => $t): ?>
      <div class="card reveal reveal-delay-<?= $i+1 ?>">
        <div style="margin-bottom:1rem;font-size:1.1rem"><?= $t[4] ?></div>
        <p style="font-style:italic;margin-bottom:1.25rem;font-size:.95rem">"<?= $t[3] ?>"</p>
        <div style="display:flex;align-items:center;gap:.75rem">
          <div style="width:40px;height:40px;border-radius:50%;background:var(--clr-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;flex-shrink:0">
            <?= $t[0][0] ?>
          </div>
          <div>
            <div style="font-weight:600;font-size:.9rem"><?= $t[0] ?></div>
            <div style="font-size:.8rem;color:var(--clr-muted)"><?= $t[1] ?> · <?= $t[2] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CONFIANCE ── -->
<section class="section-sm" style="background:var(--clr-primary);color:#fff">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:2rem;text-align:center">
      <?php foreach([
        ['🌱','Ingrédients 100% Naturels'],['🏆','Certifié Bio & Vegan'],
        ['🚚','Livraison rapide en France'],['💬','Support client 7j/7']
      ] as $t): ?>
      <div class="reveal">
        <div style="font-size:2rem;margin-bottom:.5rem"><?= $t[0] ?></div>
        <div style="font-weight:600;font-size:.9rem;color:rgba(255,255,255,.9)"><?= $t[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA FINAL ── -->
<section class="section" style="background:#fff;text-align:center">
  <div class="container">
    <div class="reveal" style="max-width:600px;margin:0 auto">
      <h2 style="margin-bottom:1rem">Prêt à prendre soin de vous ?</h2>
      <p style="margin-bottom:2rem">Rejoignez plus de 2 000 clients qui ont fait confiance à Vitanova pour retrouver leur équilibre naturel.</p>
      <a href="boutique.php" class="btn btn-primary btn-lg">Explorer la boutique</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
