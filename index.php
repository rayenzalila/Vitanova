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
<section class="hero parallax-hero" style="position:relative; overflow:hidden">
  <!-- Background Decoration Image -->
  <div class="page-bg-decoration hide-mobile" style="
      position: absolute;
      right: 32%;
      bottom: 55%;
      width: 200px;
      height: 200px;
      background-image: url('images/Design%20sans%20titre%20(3).png');
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
      opacity: 0.6;
      z-index: 1;
      pointer-events: none;
      filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
  "></div>

  <div class="botanical-pattern" style="z-index:0">
    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
      <circle cx="10%" cy="20%" r="180" fill="var(--pattern-1)"/>
      <circle cx="85%" cy="70%" r="240" fill="var(--pattern-2)"/>
      <circle cx="60%" cy="10%" r="120" fill="var(--pattern-1)"/>
      <circle cx="5%" cy="80%" r="100" fill="var(--pattern-2)"/>
    </svg>
  </div>
  <div class="parallax-bg" aria-hidden="true">
    <svg viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%">
      <path d="M400,300 Q450,200 500,300 Q450,400 400,300Z" fill="var(--pattern-1)"/>
      <path d="M200,400 Q250,300 300,400 Q250,500 200,400Z" fill="var(--pattern-2)"/>
      <path d="M600,150 Q650,50 700,150 Q650,250 600,150Z" fill="var(--pattern-1)"/>
    </svg>
  </div>

  <div class="container" style="position:relative;z-index:2;padding-top:0;padding-bottom:2rem;min-height:calc(100vh - var(--nav-h));display:flex;align-items:center">
    <div style="max-width:650px;position:relative;z-index:2;margin-top:-5vh">
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
      <div class="anim-fade-up anim-delay-4" style="display:flex;gap:2rem;margin-top:2.5rem;flex-wrap:wrap;justify-content:flex-start">
        <?php foreach([['2 000+','Clients satisfaits'],['100%','Ingrédients naturels'],['Certifié','Bio & Vegan']] as $stat): ?>
        <div class="text-center-mobile">
          <div style="font-size:1.4rem;font-weight:800;color:var(--clr-primary)"><?= $stat[0] ?></div>
          <div style="font-size:.85rem;color:var(--clr-muted)"><?= $stat[1] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <!-- Image en position absolue pour ne pas pousser le texte -->
    <div class="anim-fade-up anim-delay-2 hide-mobile" style="position:absolute;right:-5%;bottom:5%;width:55%;max-width:600px;z-index:1;pointer-events:none">
      <div class="parallax-element" data-speed="0.1">
        <img src="images/New Project (1).png" alt="Expert Vitanova" style="width:100%;height:auto;display:block;filter:drop-shadow(0 30px 60px rgba(0,0,0,0.1))">
      </div>
    </div>
  </div>
</section>

<!-- ── BIENFAITS ── -->
<section class="section" id="bienfaits" style="background:var(--clr-bg)">

  <div class="container">
    <div class="text-center reveal" style="margin-bottom:3.5rem">
      <h2 class="section-title">Nos bienfaits naturels</h2>
      <p class="section-sub">Chaque formule est pensée pour un objectif précis, avec des actifs cliniquement reconnus.</p>
    </div>
    <div class="grid-responsive-4">
      <?php
      $bienfaits = [
        ['🧘','Stress & Anxiété','Retrouvez la sérénité avec nos adaptogènes naturels qui régulent le cortisol.','stress'],
        ['🌙','Sommeil','Endormissement rapide et sommeil réparateur grâce à nos plantes sédatives douces.','sommeil'],
        ['⚡','Énergie & Focus','Boost naturel d\'énergie et concentration optimale sans nervosité ni crash.','energie'],
        ['🌿','Bien-être','Soutien global de l\'organisme pour une vitalité rayonnante au quotidien.','bien-etre'],
      ];
      foreach ($bienfaits as $i => $b): ?>
      <a href="boutique.php?cat=<?= $b[3] ?>" class="card reveal reveal-delay-<?= $i+1 ?> text-center-mobile" style="text-decoration:none;cursor:pointer">
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
          <?= renderProductImage($p) ?>
        </a>
        <div class="product-card__body">
          <span class="badge badge--cat" style="margin-bottom:.5rem"><?= categoryLabel($p['category']) ?></span>
          <h3 class="product-card__name"><?= htmlspecialchars($p['name']) ?></h3>
          <p class="product-card__desc"><?= htmlspecialchars($p['short_description']) ?></p>
          <div class="product-card__footer">
            <span class="product-card__price"><?= formatPrice($p['price']) ?></span>
            <button type="button" class="btn btn-primary btn-sm" data-add-to-cart
              data-product-id="<?= $p['id'] ?>"
              data-product-name="<?= htmlspecialchars($p['name']) ?>"
              data-product-price="<?= $p['price'] ?>"
              data-product-category="<?= $p['category'] ?>"
              data-product-slug="<?= $p['slug'] ?>"
              data-product-stock="<?= $p['stock'] ?>"
              data-product-image="<?= htmlspecialchars(productImageUrl($p)) ?>">
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
<section class="section" style="background:var(--clr-bg)">

  <div class="container">
    <div class="text-center reveal" style="margin-bottom:3rem">
      <h2 class="section-title">Ce que disent nos clients</h2>
      <p class="section-sub">Des milliers de personnes font confiance à Vitanova chaque jour.</p>
    </div>
    <div class="grid-responsive-3">
      <?php
      $testimonials = [
        ['Marie L.','Paris','Sérénia Zen','Après 3 semaines, mon niveau de stress a vraiment diminué. Je me sens beaucoup plus calme au travail. Je recommande vivement !','⭐⭐⭐⭐⭐'],
        ['Thomas B.','Lyon','Noctalis Sommeil','Enfin un produit qui fonctionne vraiment pour le sommeil ! Je m\'endors en 20 minutes et je me réveille reposé.','⭐⭐⭐⭐⭐'],
        ['Sophie M.','Bordeaux','Vitalys Énergie','Mon énergie a décollé sans les effets secondaires des boissons énergisantes. Naturel et efficace !','⭐⭐⭐⭐⭐'],
      ];
      foreach ($testimonials as $i => $t): ?>
      <div class="card reveal reveal-delay-<?= $i+1 ?> text-center-mobile">
        <div style="margin-bottom:1rem;font-size:1.1rem"><?= $t[4] ?></div>
        <p style="font-style:italic;margin-bottom:1.25rem;font-size:.95rem">"<?= $t[3] ?>"</p>
        <div style="display:flex;align-items:center;gap:.75rem;justify-content:center">
          <div style="width:40px;height:40px;border-radius:50%;background:var(--clr-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;flex-shrink:0">
            <?= $t[0][0] ?>
          </div>
          <div style="text-align:left">
            <div style="font-weight:600;font-size:.9rem"><?= $t[0] ?></div>
            <div style="font-size:.8rem;color:var(--clr-muted)"><?= $t[1] ?> · <?= $t[2] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CONFIANCE (Marquee Animé) ── -->
<section style="background:var(--clr-primary);color:#fff;border-y:1px solid rgba(255,255,255,.1)">
  <div class="marquee">
    <?php 
    $items = [
      ['🌱','Ingrédients 100% Naturels'],['🏆','Certifié Bio & Vegan'],
      ['🚚','Livraison rapide en France'],['💬','Support client 7j/7'],
      ['🧪','Testé en Laboratoire'],['🌍','Eco-responsable']
    ];
    // Affichage double pour l'effet infini
    for($i=0; $i<2; $i++): ?>
    <div class="marquee__content">
      <?php foreach($items as $t): ?>
      <div class="marquee__item">
        <span style="font-size:1.5rem"><?= $t[0] ?></span>
        <span style="font-weight:600;font-size:.9rem;text-transform:uppercase;letter-spacing:.05em"><?= $t[1] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endfor; ?>
  </div>
</section>

<!-- ── CTA FINAL ── -->
<section class="section" style="background:var(--clr-bg);text-align:center">

  <div class="container">
    <div class="reveal" style="max-width:600px;margin:0 auto">
      <h2 style="margin-bottom:1rem">Prêt à prendre soin de vous ?</h2>
      <p style="margin-bottom:2rem">Rejoignez plus de 2 000 clients qui ont fait confiance à Vitanova pour retrouver leur équilibre naturel.</p>
      <a href="boutique.php" class="btn btn-primary btn-lg">Explorer la boutique</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
