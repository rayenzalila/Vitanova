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
    $name  = trim($_POST['name'] ?? '');
    $slug  = trim($_POST['slug'] ?? '') ?: slugify($name);
    $short = trim($_POST['short_description'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $ben   = trim($_POST['benefits'] ?? '');
    $ing   = trim($_POST['ingredients'] ?? '');
    $price = $_POST['price'] ?? '';
    $cat   = $_POST['category'] ?? '';
    $stock = $_POST['stock'] ?? '';

    if (!$name || !$price || !$cat || !$stock) { $msg = MSG_ADMIN_REQUIRED_FIELD; $msgType = 'error'; }
    elseif (!is_numeric($price) || (float)$price <= 0) { $msg = MSG_ADMIN_PRICE_INVALID; $msgType = 'error'; }
    elseif (!is_numeric($stock) || (int)$stock < 0) { $msg = MSG_ADMIN_STOCK_INVALID; $msgType = 'error'; }
    else {
        try {
            if ($pid) {
                $stmt = $db->prepare("UPDATE products SET name=?,slug=?,short_description=?,description=?,benefits=?,ingredients=?,price=?,category=?,stock=? WHERE id=?");
                $stmt->execute([$name,$slug,$short,$desc,$ben,$ing,(float)$price,$cat,(int)$stock,$pid]);
                $msg = MSG_ADMIN_PRODUCT_UPDATED;
            } else {
                $stmt = $db->prepare("INSERT INTO products (name,slug,short_description,description,benefits,ingredients,price,category,stock) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$name,$slug,$short,$desc,$ben,$ing,(float)$price,$cat,(int)$stock]);
                $msg = MSG_ADMIN_PRODUCT_ADDED;
            }
        } catch(Exception $e) { $msg = MSG_GENERIC_SERVER_ERROR; $msgType = 'error'; }
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
<style>
body{display:flex;min-height:100vh;background:var(--clr-surface)}
.admin-sidebar{width:240px;background:var(--clr-primary);color:#fff;flex-shrink:0;padding:2rem 0;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.admin-sidebar .logo{padding:0 1.5rem 2rem;font-size:1.25rem;font-weight:800;border-bottom:1px solid rgba(255,255,255,.15)}
.admin-sidebar .logo span{color:var(--clr-accent)}
.admin-nav a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.5rem;color:rgba(255,255,255,.8);font-size:.9rem;font-weight:500;transition:all .2s;border-left:3px solid transparent}
.admin-nav a:hover,.admin-nav a.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--clr-accent)}
.admin-main{flex:1;padding:2rem;overflow-x:hidden;max-width:100%}
.admin-table{background:#fff;border:1px solid var(--clr-border);border-radius:var(--radius-lg);overflow:hidden;margin-top:1.5rem}
.admin-table table{width:100%;border-collapse:collapse;font-size:.875rem}
.admin-table th{background:var(--clr-surface);padding:.875rem 1rem;text-align:left;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--clr-muted);border-bottom:2px solid var(--clr-border)}
.admin-table td{padding:.75rem 1rem;border-bottom:1px solid var(--clr-border)}
.admin-table tr:hover td{background:var(--clr-surface)}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}
.modal{background:#fff;border-radius:var(--radius-lg);padding:2rem;width:100%;max-width:640px;max-height:90vh;overflow-y:auto}
</style>
</head>
<body>
<aside class="admin-sidebar">
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

<main class="admin-main">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem">Gestion des produits</h1>
    <button class="btn btn-primary" onclick="document.getElementById('product-modal').style.display='flex'">+ Ajouter un produit</button>
  </div>

  <?php if($msg): ?>
  <div class="alert alert--<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="admin-table">
    <table>
      <thead><tr><th>ID</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($products as $p): ?>
      <tr>
        <td style="color:var(--clr-muted)">#<?= $p['id'] ?></td>
        <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><span style="font-size:.78rem;color:var(--clr-muted)"><?= htmlspecialchars($p['short_description']) ?></span></td>
        <td><span class="badge badge--cat"><?= categoryLabel($p['category']) ?></span></td>
        <td style="font-weight:700;color:var(--clr-primary)"><?= formatPrice($p['price']) ?></td>
        <td><span style="<?= $p['stock'] < 10 ? 'color:var(--clr-error);font-weight:600' : '' ?>"><?= $p['stock'] ?></span></td>
        <td style="display:flex;gap:.5rem;flex-wrap:wrap">
          <a href="produits.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
          <a href="produits.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm" style="background:var(--clr-error-bg);color:var(--clr-error);border:1px solid var(--clr-error)"
            onclick="return confirm('Supprimer ce produit ?')">Supprimer</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Modal ajout/édition -->
<div class="modal-overlay" id="product-modal" style="display:<?= ($editProduct || (isset($_POST['name']) && $msgType==='error')) ? 'flex' : 'none' ?>">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
      <h2 style="font-size:1.15rem"><?= $editProduct ? 'Modifier le produit' : 'Ajouter un produit' ?></h2>
      <button onclick="document.getElementById('product-modal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:1.5rem;color:var(--clr-muted)">×</button>
    </div>
    <form method="POST">
      <?php if($editProduct): ?><input type="hidden" name="id" value="<?= $editProduct['id'] ?>"><?php endif; ?>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editProduct['slug'] ?? '') ?>" placeholder="auto-généré">
        </div>
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
      <div class="form-row">
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
      <div style="display:flex;gap:1rem;margin-top:1rem">
        <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Mettre à jour' : 'Ajouter' ?></button>
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('product-modal').style.display='none'">Annuler</button>
      </div>
    </form>
  </div>
</div>
</body></html>
