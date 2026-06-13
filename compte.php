<?php
// ── Core Imports & Session ──────────
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/messages.php';

$errors = []; $successMsg = '';

// ── Déconnexion
if (($_GET['action'] ?? '') === 'logout') {
    logoutUser();
    setFlash('success', MSG_LOGOUT_SUCCESS);
    header('Location: index.php');
    exit;
}

// ── CONNEXION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) { $errors[] = MSG_GENERIC_SERVER_ERROR; }
    else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (!$email) $errors[] = MSG_LOGIN_EMAIL_EMPTY;
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = MSG_LOGIN_EMAIL_INVALID;
        if (!$pass)  $errors[] = MSG_LOGIN_PASSWORD_EMPTY;
        if (empty($errors)) {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if (!$user) $errors[] = MSG_LOGIN_ACCOUNT_NOT_FOUND;
                elseif (!password_verify($pass, $user['password'])) $errors[] = MSG_LOGIN_WRONG_CREDENTIALS;
                else { loginUser($user); setFlash('success', MSG_LOGIN_SUCCESS); header('Location: index.php?login_success=1'); exit; }
            } catch (Exception $e) { $errors[] = MSG_GENERIC_SERVER_ERROR; }
        }
    }
}

// ── INSCRIPTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) { $errors[] = MSG_GENERIC_SERVER_ERROR; }
    else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $pass    = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if (!$name)    $errors[] = MSG_REGISTER_NAME_EMPTY;
        elseif (mb_strlen($name) < 2) $errors[] = MSG_REGISTER_NAME_SHORT;
        if (!$email)   $errors[] = MSG_REGISTER_EMAIL_EMPTY;
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = MSG_LOGIN_EMAIL_INVALID;
        if (!$pass)    $errors[] = MSG_REGISTER_PASSWORD_EMPTY;
        elseif (strlen($pass) < 8) $errors[] = MSG_REGISTER_PASSWORD_SHORT;
        if (!$confirm) $errors[] = MSG_REGISTER_CONFIRM_EMPTY;
        elseif ($pass !== $confirm) $errors[] = MSG_REGISTER_PASSWORD_MISMATCH;
        if (empty($errors)) {
            try {
                $db = getDB();
                $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
                $chk->execute([$email]);
                if ($chk->fetch()) { $errors[] = MSG_REGISTER_EMAIL_TAKEN; }
                else {
                    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12]);
                    $ins  = $db->prepare("INSERT INTO users (name, email, password) VALUES (?,?,?)");
                    $ins->execute([htmlspecialchars($name), $email, $hash]);
                    $user = $db->prepare("SELECT * FROM users WHERE email = ?");
                    $user->execute([$email]);
                    loginUser($user->fetch());
                    setFlash('success', MSG_REGISTER_SUCCESS);
                    header('Location: compte.php?login_success=1'); exit;
                }
            } catch (Exception $e) { $errors[] = MSG_REGISTER_SERVER_ERROR; }
        }
    }
}

$user = currentUser();
$pageTitle = 'Mon Compte — Vitanova';
require_once 'includes/header.php';
?>


<div class="page-header"><div class="container">
  <h1><?= $user ? 'Mon Compte' : 'Connexion / Inscription' ?></h1>
</div></div>

<section class="section-sm" style="background:var(--clr-bg)">
<div class="container" style="max-width:900px">

<?php if (!empty($errors)): ?>
<div class="alert alert--error" style="margin-bottom:1.5rem">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
  <ul style="margin:0;padding:0 0 0 .5rem;list-style:none"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<?php if ($user): ?>
  <!-- DASHBOARD -->
  <div style="margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;gap:2rem;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:1.5rem;flex:1;min-width:300px" class="text-center-mobile">
      <img src="mascots/Welcome-Back(when%20loging%20in).png" alt="Welcome Mascot" style="height:150px;width:auto;display:block">
      <div>
        <h2 style="margin-bottom:.25rem">Bonjour, <?= htmlspecialchars($user['name']) ?> 👋</h2>
        <p style="color:var(--clr-muted);font-size:.9rem"><?= htmlspecialchars($user['email']) ?></p>
      </div>
    </div>
    <style>
    @keyframes pulseDanger {
      0% { box-shadow: 0 0 0 0 rgba(163, 45, 45, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(163, 45, 45, 0); }
      100% { box-shadow: 0 0 0 0 rgba(163, 45, 45, 0); }
    }
    .btn-deconnexion {
      background: transparent;
      color: var(--clr-error);
      border-color: var(--clr-border);
      transition: all 0.3s ease;
    }
    .btn-deconnexion:hover {
      background: var(--error-bg, #fcebeb);
      color: var(--clr-error);
      border-color: var(--clr-error);
      transform: translateY(-2px);
      animation: pulseDanger 1.5s infinite;
    }
    </style>
    <a href="compte.php?action=logout" class="btn btn-deconnexion">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      Déconnexion
    </a>
  </div>

  <?php 
  $isAdmin = $user['role'] === 'admin';
  $tabsList = $isAdmin ? ['Mes informations'] : ['Mes commandes', 'Mes informations'];
  ?>
  <div class="tabs">
    <div style="display:flex;border-bottom:2px solid var(--clr-border);margin-bottom:2rem;overflow-x:auto;white-space:nowrap;scrollbar-width:none">
      <style>.tabs div::-webkit-scrollbar { display: none; }</style>
      <?php foreach($tabsList as $i=>$tab): ?>
      <button class="tab-btn <?= $i===0?'active':'' ?>"><?= $tab ?></button>
      <?php endforeach; ?>
    </div>

    <?php if (!$isAdmin): ?>
    <!-- Commandes -->
    <div class="tab-content">
      <?php
      try {
        $db = getDB();
        $orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $orders->execute([$user['id']]);
        $orders = $orders->fetchAll();
      } catch(Exception $e) { $orders = []; }
      ?>
      <?php if (empty($orders)): ?>
      <p style="color:var(--clr-muted);font-style:italic">Vous n'avez pas encore passé de commande. <a href="boutique.php" style="color:var(--clr-primary)">Découvrir nos produits →</a></p>
      <?php else: ?>
      <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:.9rem">
        <thead><tr style="background:var(--clr-surface)">
          <?php foreach(['N°','Date','Total','Statut','Détails'] as $h): ?>
          <th style="padding:.75rem 1rem;text-align:left;border-bottom:2px solid var(--clr-border);font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:var(--clr-muted)"><?= $h ?></th>
          <?php endforeach; ?>
        </tr></thead>
        <tbody>
        <?php foreach($orders as $o): ?>
        <tr style="border-bottom:1px solid var(--clr-border)">
          <td style="padding:.75rem 1rem;font-weight:700">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
          <td style="padding:.75rem 1rem;color:var(--clr-muted)"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
          <td style="padding:.75rem 1rem;font-weight:600;color:var(--clr-primary)"><?= formatPrice($o['total']) ?></td>
          <td style="padding:.75rem 1rem"><?= orderStatusBadge($o['status']) ?></td>
          <td style="padding:.75rem 1rem"><a href="confirmation.php?id=<?= $o['id'] ?>" style="color:var(--clr-primary);font-size:.85rem">Voir →</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Infos -->
    <div class="tab-content">
      <div class="card" style="max-width:460px">
        <h3 style="margin-bottom:1.25rem">Mes informations</h3>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p style="margin-top:.5rem"><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p style="margin-top:.5rem"><strong>Rôle :</strong> <?= $isAdmin ? 'Administrateur' : 'Client' ?></p>
        <?php if ($isAdmin): ?>
        <a href="admin/index.php" class="btn btn-primary btn-block" style="margin-top:1.25rem">Accéder au panneau d'administration</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- LOGIN / REGISTER -->
  <div class="grid-responsive-2" style="grid-template-columns:1fr 1fr;gap:2rem">

    <!-- Connexion -->
    <div class="card">
      <h2 style="font-size:1.2rem;margin-bottom:1.5rem">Se connecter</h2>
      <form method="POST" data-validate-form>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label class="form-label" for="login-email">Email</label>
          <input type="email" id="login-email" name="email" class="form-control" placeholder="jean@exemple.fr"
            data-validate="required|email" data-msg-required="<?= MSG_LOGIN_EMAIL_EMPTY ?>" data-msg-email="<?= MSG_LOGIN_EMAIL_INVALID ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="login-pass">Mot de passe</label>
          <input type="password" id="login-pass" name="password" class="form-control" placeholder="••••••••"
            data-validate="required" data-msg-required="<?= MSG_LOGIN_PASSWORD_EMPTY ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
      </form>
    </div>

    <!-- Inscription -->
    <div class="card">
      <h2 style="font-size:1.2rem;margin-bottom:1.5rem">Créer un compte</h2>
      <form method="POST" data-validate-form>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="register">
        <div class="form-group">
          <label class="form-label" for="reg-name">Nom complet</label>
          <input type="text" id="reg-name" name="name" class="form-control" placeholder="Jean Dupont"
            data-validate="required|min:2" data-msg-required="<?= MSG_REGISTER_NAME_EMPTY ?>" data-msg-min="<?= MSG_REGISTER_NAME_SHORT ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-email">Email</label>
          <input type="email" id="reg-email" name="email" class="form-control" placeholder="jean@exemple.fr"
            data-validate="required|email" data-msg-required="<?= MSG_REGISTER_EMAIL_EMPTY ?>" data-msg-email="<?= MSG_LOGIN_EMAIL_INVALID ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-pass">Mot de passe</label>
          <input type="password" id="reg-pass" name="password" class="form-control" placeholder="Min. 8 caractères"
            data-validate="required|min:8" data-msg-required="<?= MSG_REGISTER_PASSWORD_EMPTY ?>" data-msg-min="<?= MSG_REGISTER_PASSWORD_SHORT ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-confirm">Confirmer le mot de passe</label>
          <input type="password" id="reg-confirm" name="confirm" class="form-control" placeholder="••••••••"
            data-validate="required|match:reg-pass" data-msg-required="<?= MSG_REGISTER_CONFIRM_EMPTY ?>" data-msg-match="<?= MSG_REGISTER_PASSWORD_MISMATCH ?>">
        </div>
        <button type="submit" class="btn btn-outline btn-block">Créer mon compte</button>
      </form>
    </div>
  </div>
<?php endif; ?>

</div>
</section>
<?php require_once 'includes/footer.php'; ?>
