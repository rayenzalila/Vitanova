<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
requireAdmin();

$db = getDB();
$msg = ''; $msgType = 'success';

// Supprimer
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $chk = $db->prepare("SELECT COUNT(*) FROM orders WHERE JSON_SEARCH(items,'one',?) IS NOT NULL");
        // simplified: check if product id appears in orders
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $msg = MSG_ADMIN_PRODUCT_DELETED;
    } catch(Exception $e) { $msg = MSG_ADMIN_PRODUCT_DELETE_ERROR; $msgType = 'error'; }
}

// Ajouter / Modifier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid   = (int)($_POST['id'] ?? 0);
    $manual_id = (int)($_POST['manual_id'] ?? 0);

    $name  = trim($_POST['name'] ?? '');
    $slug  = trim($_POST['slug'] ?? '') ?: slugify($name);
    $short = trim($_POST['short_description'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $ben   = trim($_POST['benefits'] ?? '');
    $ing   = trim($_POST['ingredients'] ?? '');
    $price = $_POST['price'] ?? '';
    $cat   = $_POST['category'] ?? '';
    $stock = $_POST['stock'] ?? '';

    // ── Image Upload ─────────────────────────────────
    $imageFilename = null; // null = don't change existing
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        $file    = $_FILES['image'];
        if (!in_array($file['type'], $allowed)) {
            $msg = 'Format image non autorisé (JPG, PNG, WEBP, GIF uniquement).'; $msgType = 'error';
        } elseif ($file['size'] > $maxSize) {
            $msg = 'Image trop lourde (max 5 Mo).'; $msgType = 'error';
        } else {
            $ext           = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $imageFilename = uniqid('prod_', true) . '.' . $ext;
            $dest          = dirname(__DIR__) . '/assets/img/products/' . $imageFilename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $msg = 'Erreur lors de l\'upload de l\'image.'; $msgType = 'error';
                $imageFilename = null;
            }
        }
    }

    if (!$msg) {
        if (!$name || !$price || !$cat || !$stock) { $msg = MSG_ADMIN_REQUIRED_FIELD; $msgType = 'error'; }
        elseif (!is_numeric($price) || (float)$price <= 0) { $msg = MSG_ADMIN_PRICE_INVALID; $msgType = 'error'; }
        elseif (!is_numeric($stock) || (int)$stock < 0) { $msg = MSG_ADMIN_STOCK_INVALID; $msgType = 'error'; }
        else {
            try {
                if ($pid) {
                    if ($imageFilename !== null) {
                        // Delete old image file if it exists
                        $old = $db->prepare("SELECT image_url FROM products WHERE id=?");
                        $old->execute([$pid]);
                        $oldImg = $old->fetchColumn();
                        if ($oldImg) @unlink(dirname(__DIR__) . '/assets/img/products/' . $oldImg);

                        $stmt = $db->prepare("UPDATE products SET name=?,slug=?,short_description=?,description=?,benefits=?,ingredients=?,price=?,category=?,stock=?,image_url=? WHERE id=?");
                        $stmt->execute([$name,$slug,$short,$desc,$ben,$ing,(float)$price,$cat,(int)$stock,$imageFilename,$pid]);
                    } else {
                        $stmt = $db->prepare("UPDATE products SET name=?,slug=?,short_description=?,description=?,benefits=?,ingredients=?,price=?,category=?,stock=? WHERE id=?");
                        $stmt->execute([$name,$slug,$short,$desc,$ben,$ing,(float)$price,$cat,(int)$stock,$pid]);
                    }
                    $msg = MSG_ADMIN_PRODUCT_UPDATED;
                } else {
                    if ($manual_id > 0) {
                        // Check if ID exists
                        $chk = $db->prepare("SELECT id FROM products WHERE id = ?");
                        $chk->execute([$manual_id]);
                        if ($chk->fetch()) {
                            $msg = "L'ID $manual_id est déjà utilisé."; $msgType = 'error';
                        } else {
                            $stmt = $db->prepare("INSERT INTO products (id, name, slug, short_description, description, benefits, ingredients, price, category, stock, image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                            $stmt->execute([$manual_id, $name, $slug, $short, $desc, $ben, $ing, (float)$price, $cat, (int)$stock, $imageFilename]);
                            $msg = MSG_ADMIN_PRODUCT_ADDED;
                        }
                    } else {
                        $stmt = $db->prepare("INSERT INTO products (name,slug,short_description,description,benefits,ingredients,price,category,stock,image_url) VALUES (?,?,?,?,?,?,?,?,?,?)");
                        $stmt->execute([$name,$slug,$short,$desc,$ben,$ing,(float)$price,$cat,(int)$stock,$imageFilename]);
                        $msg = MSG_ADMIN_PRODUCT_ADDED;
                    }
                }
            } catch(Exception $e) { $msg = MSG_GENERIC_SERVER_ERROR; $msgType = 'error'; }
        }
    }
}

$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$editProduct = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM products WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editProduct = $s->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Produits Admin — Vitanova</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script>
  (function() {
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', savedTheme);
  })();
</script>
<body class="admin-body">
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="logo">Vita<span>nova</span> Admin</div>
  <nav class="admin-nav" style="margin-top:1rem;flex:1">
    <a href="index.php">📊 Tableau de bord</a>
    <a href="produits.php" class="active">📦 Produits</a>
    <a href="commandes.php">🛒 Commandes</a>
    <a href="utilisateurs.php">👥 Utilisateurs</a>
    <a href="messages.php">✉️ Messages</a>
  </nav>
  <div style="padding:1.5rem;border-top:1px solid rgba(255,255,255,.15)">
    <a href="../" style="color:rgba(255,255,255,.7);font-size:.85rem">← Voir le site</a>
  </div>
</aside>

<div class="admin-sidebar-overlay" id="sidebar-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:none"></div>

<main class="admin-main">
  <div class="admin-header">
    <button class="admin-burger" id="admin-burger" aria-label="Menu">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <h1>Gestion des produits</h1>
    <div class="admin-header-actions" style="display:flex;gap:0.75rem;align-items:center">
      <button class="theme-toggle" id="theme-toggle">
        <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="sun-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <button class="btn btn-primary" onclick="document.getElementById('product-modal').style.display='flex'">+ Produit</button>
    </div>
  </div>

  <?php if($msg): ?>
  <div class="alert alert--<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Image</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($products as $p): ?>
      <tr>
        <td style="color:var(--clr-muted)">#<?= $p['id'] ?></td>
        <td>
          <?php if (!empty($p['image_url'])): ?>
            <img src="<?= BASE_URL ?>/assets/img/products/<?= htmlspecialchars($p['image_url']) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--clr-border)">
          <?php else: ?>
            <div style="width:56px;height:56px;border-radius:8px;background:var(--clr-border);display:flex;align-items:center;justify-content:center;font-size:1.4rem">📦</div>
          <?php endif; ?>
        </td>
        <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><span style="font-size:.78rem;color:var(--clr-muted)"><?= htmlspecialchars($p['short_description']) ?></span></td>
        <td><span class="badge badge--cat"><?= categoryLabel($p['category']) ?></span></td>
        <td style="font-weight:700;color:var(--clr-primary)"><?= formatPrice($p['price']) ?></td>
        <td><span style="<?= $p['stock'] < 10 ? 'color:var(--clr-error);font-weight:600' : '' ?>"><?= $p['stock'] ?></span></td>
        <td style="text-align:right;vertical-align:middle">
          <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:flex-end;align-items:center">
            <a href="produits.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
            <button class="btn btn-sm" style="background:var(--clr-error-bg);color:var(--clr-error);border:1px solid var(--clr-error)"
              onclick="confirmDelete('produits.php?action=delete&id=<?= $p['id'] ?>')">Supprimer</button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirm-modal" style="display:none">
  <div class="confirm-modal">
    <span class="confirm-modal__icon">⚠️</span>
    <h3 class="confirm-modal__title">Confirmation</h3>
    <p class="confirm-modal__text">Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.</p>
    <div class="confirm-modal__actions">
      <button class="btn btn-ghost" onclick="closeConfirm()">Annuler</button>
      <a href="#" id="confirm-delete-btn" class="btn" style="background:var(--clr-error);color:#fff">Supprimer</a>
    </div>
  </div>
</div>

<script>
const burger = document.getElementById('admin-burger');
const sidebar = document.getElementById('admin-sidebar');
const overlay = document.getElementById('sidebar-overlay');
function toggleSidebar() { sidebar.classList.toggle('open'); overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none'; }
burger.addEventListener('click', toggleSidebar);
overlay.addEventListener('click', toggleSidebar);

// Confirmation Modal Logic
const confirmModal = document.getElementById('confirm-modal');
const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

function confirmDelete(url) {
  confirmDeleteBtn.href = url;
  confirmModal.style.display = 'flex';
}

function closeConfirm() {
  confirmModal.style.display = 'none';
}

// Theme Toggle logic
document.getElementById('theme-toggle').addEventListener('click', () => {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
});
</script>

<!-- Modal ajout/édition -->
<div class="modal-overlay" id="product-modal" style="display:<?= ($editProduct || (isset($_POST['name']) && $msgType==='error')) ? 'flex' : 'none' ?>">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('product-modal').style.display='none'">&times;</button>
    <h2 style="margin-bottom:1.5rem;font-size:1.25rem"><?= $editProduct ? 'Modifier le produit' : 'Ajouter un produit' ?></h2>
    
    <form method="POST" enctype="multipart/form-data">
      <?php if($editProduct): ?><input type="hidden" name="id" value="<?= $editProduct['id'] ?>"><?php endif; ?>
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">ID <?= !$editProduct ? '(optionnel)' : '' ?></label>
          <input type="number" name="manual_id" class="form-control" value="<?= $editProduct['id'] ?? '' ?>" <?= $editProduct ? 'disabled' : '' ?> placeholder="<?= !$editProduct ? 'Auto' : '' ?>">
        </div>
        <div class="form-group" style="grid-column: span 2">
          <label class="form-label">Nom *</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editProduct['slug'] ?? '') ?>" placeholder="auto-généré">
      </div>
      <div class="form-group">
        <label class="form-label">Description courte</label>
        <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($editProduct['short_description'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Description complète</label>
        <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Bienfaits (un par ligne)</label>
        <textarea name="benefits" rows="3" class="form-control"><?= htmlspecialchars($editProduct['benefits'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Ingrédients</label>
        <textarea name="ingredients" rows="3" class="form-control"><?= htmlspecialchars($editProduct['ingredients'] ?? '') ?></textarea>
      </div>
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Prix (TND) *</label>
          <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?= $editProduct['price'] ?? '' ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Stock *</label>
          <input type="number" name="stock" min="0" class="form-control" value="<?= $editProduct['stock'] ?? 100 ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Catégorie *</label>
        <select name="category" class="form-control" required>
          <?php foreach(['stress'=>'Stress & Anxiété','sommeil'=>'Sommeil','energie'=>'Énergie & Focus','bien-etre'=>'Bien-être'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($editProduct['category'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Image du produit</label>
        <?php if (!empty($editProduct['image_url'])): ?>
          <div style="margin-bottom:.75rem">
            <img src="<?= BASE_URL ?>/assets/img/products/<?= htmlspecialchars($editProduct['image_url']) ?>" alt="" style="height:80px;border-radius:8px;border:1px solid var(--clr-border);object-fit:cover">
            <p style="font-size:.78rem;color:var(--clr-muted);margin-top:.3rem">Image actuelle — téléversez-en une nouvelle pour la remplacer.</p>
          </div>
        <?php endif; ?>
        <input type="file" name="image" id="image-input" accept="image/jpeg,image/png,image/webp,image/gif" class="form-control" style="padding:.4rem">
        <p style="font-size:.75rem;color:var(--clr-muted);margin-top:.35rem">JPG, PNG, WEBP ou GIF — max 5 Mo</p>
        <img id="image-preview" src="" alt="Aperçu" style="display:none;margin-top:.75rem;max-height:120px;border-radius:8px;border:1px solid var(--clr-border);object-fit:cover">
      </div>
      <div style="display:flex;gap:1rem;margin-top:1.5rem">
        <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Mettre à jour' : 'Ajouter' ?></button>
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('product-modal').style.display='none'">Annuler</button>
      </div>
    </form>
  </div>
</div>
<script>
// Image preview on file select
document.getElementById('image-input')?.addEventListener('change', function() {
  const preview = document.getElementById('image-preview');
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(this.files[0]);
  } else {
    preview.src = ''; preview.style.display = 'none';
  }
});
</script>
</body></html>
