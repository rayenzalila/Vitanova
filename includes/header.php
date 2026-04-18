<?php
/**
 * Vitanova — Header partagé
 * Inclure au début de chaque page : require_once 'includes/header.php';
 * Variables attendues: $pageTitle, $pageDesc (optionnel)
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/messages.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Vitanova — Compléments Naturels Bio';
$pageDesc  = $pageDesc  ?? 'Vitanova, votre marque de compléments alimentaires bio premium pour le stress, le sommeil, l\'énergie et le bien-être général.';
$csrfToken = generateCsrfToken();

// Flash message passé au JS
$flashMsg  = getFlash('success') ?? getFlash('error') ?? '';
$flashType = isset($_SESSION['_flash_was_error']) ? 'error' : 'success';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
  <meta name="theme-color" content="#0f6e56">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body>

<?php if ($flashMsg): ?>
<div id="php-flash" data-message="<?= htmlspecialchars($flashMsg) ?>" data-type="<?= $flashType ?>" hidden></div>
<?php endif; ?>

<!-- ── Navbar ── -->
<header class="navbar" id="navbar" role="banner">
  <div class="container navbar__inner">

    <a href="<?= BASE_URL ?>/" class="navbar__logo" aria-label="Vitanova — Accueil">
      <span class="vita">Vita</span><span class="nova">nova</span>
    </a>

    <nav class="navbar__links" aria-label="Navigation principale">
      <a href="<?= BASE_URL ?>/">Accueil</a>
      <a href="<?= BASE_URL ?>/boutique.php">Boutique</a>
      <a href="<?= BASE_URL ?>/contact.php">Contact</a>
    </nav>

    <div class="navbar__actions">
      <a href="<?= BASE_URL ?>/panier.php" class="cart-btn" aria-label="Mon panier">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span class="cart-badge" aria-live="polite">0</span>
      </a>

      <a href="<?= BASE_URL ?>/compte.php" class="cart-btn" aria-label="Mon compte">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
      </a>

      <?php if (isAdmin()): ?>
      <a href="<?= BASE_URL ?>/admin/" class="btn btn-sm btn-outline" style="font-size:.8rem;padding:.4rem .9rem">Admin</a>
      <?php endif; ?>

      <button class="hamburger" aria-label="Menu" aria-expanded="false" aria-controls="mobile-drawer">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- ── Mobile Drawer ── -->
<div class="drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="Menu mobile">
  <div class="drawer__overlay"></div>
  <div class="drawer__panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
      <span class="navbar__logo"><span class="vita" style="color:#0f6e56">Vita</span><span class="nova" style="color:#5dcaa5">nova</span></span>
      <button class="drawer__close" aria-label="Fermer le menu" style="background:none;border:none;cursor:pointer;color:#4a5568">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <nav class="drawer__links">
      <a href="<?= BASE_URL ?>/">Accueil</a>
      <a href="<?= BASE_URL ?>/boutique.php">Boutique</a>
      <a href="<?= BASE_URL ?>/contact.php">Contact</a>
      <a href="<?= BASE_URL ?>/compte.php">Mon compte</a>
      <a href="<?= BASE_URL ?>/panier.php">Mon panier</a>
    </nav>
    <?php if (isLoggedIn()): ?>
    <div style="margin-top:auto;padding-top:1rem;border-top:1px solid #e1f5ee">
      <p style="font-size:.85rem;color:#4a5568;margin-bottom:.5rem">Connecté en tant que <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>
      <a href="<?= BASE_URL ?>/compte.php?action=logout" style="font-size:.85rem;color:#a32d2d">Déconnexion</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<main id="main-content">
