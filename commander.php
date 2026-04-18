<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/messages.php';

$user = currentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = MSG_GENERIC_SERVER_ERROR;
    } else {
        $name       = trim($_POST['name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $address    = trim($_POST['address'] ?? '');
        $city       = trim($_POST['city'] ?? '');
        $postal     = trim($_POST['postal_code'] ?? '');
        $items_json = trim($_POST['cart_items'] ?? '');

        if (!$name)                                  $errors[] = MSG_CHECKOUT_NAME_EMPTY;
        if (!$email)                                 $errors[] = MSG_CHECKOUT_EMAIL_EMPTY;
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = MSG_CHECKOUT_EMAIL_INVALID;
        if (!$phone)                                 $errors[] = MSG_CHECKOUT_PHONE_EMPTY;
        elseif (!preg_match('/^(?:\+216|00216)?[234579]\d{7}$/', preg_replace('/[\s\-.]+/', '', $phone))) $errors[] = MSG_CHECKOUT_PHONE_INVALID;
        if (!$address)                               $errors[] = MSG_CHECKOUT_ADDRESS_EMPTY;
        if (!$city)                                  $errors[] = MSG_CHECKOUT_CITY_EMPTY;
        if (!$postal)                                $errors[] = MSG_CHECKOUT_POSTAL_EMPTY;
        elseif (!preg_match('/^[1-9]\d{3}$/', preg_replace('/[\s\-.]+/', '', $postal)))   $errors[] = MSG_CHECKOUT_POSTAL_INVALID;

        $items = json_decode($items_json, true);
        if (empty($items))                           $errors[] = MSG_CHECKOUT_CART_EMPTY;

        if (empty($errors)) {
            try {
                $db = getDB();
                $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
                $shipping = $subtotal >= 100 ? 0 : 7.000;
                $total    = $subtotal + $shipping;

                $stmt = $db->prepare("INSERT INTO orders (user_id,customer_name,email,phone,address,city,postal_code,items,subtotal,total,payment_method) VALUES (?,?,?,?,?,?,?,?,?,?,'livraison')");
                $stmt->execute([
                    $user['id'] ?? null,
                    htmlspecialchars($name), $email,
                    htmlspecialchars($phone), htmlspecialchars($address),
                    htmlspecialchars($city), htmlspecialchars($postal),
                    json_encode($items), $subtotal, $total
                ]);
                $orderId = $db->lastInsertId();
                header('Location: confirmation.php?id=' . $orderId);
                exit;
            } catch (Exception $e) {
                $errors[] = MSG_ORDER_SERVER_ERROR;
            }
        }
    }
}

$pageTitle = 'Passer la commande — Vitanova';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Finaliser la commande</h1>
    <p style="color:var(--clr-muted);margin-top:.5rem">Complétez vos informations de livraison.</p>
  </div>
</div>

<section class="section-sm" style="background:#fff">
  <div class="container">
    <?php if (!empty($errors)): ?>
    <div class="alert alert--error" style="max-width:700px;margin-bottom:2rem">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <ul style="margin:0;padding:0 0 0 .5rem;list-style:none"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">
      <div>
        <form method="POST" id="checkout-form" data-validate-form>
          <?= csrfField() ?>
          <input type="hidden" name="cart_items" id="cart-items-input">

          <h2 style="margin-bottom:1.5rem;font-size:1.2rem">Informations de livraison</h2>

          <div class="form-group">
            <label class="form-label" for="name">Nom complet *</label>
            <input type="text" id="name" name="name" class="form-control"
              value="<?= htmlspecialchars($user['name'] ?? $_POST['name'] ?? '') ?>"
              placeholder="Jean Dupont" autocomplete="name"
              data-validate="required"
              data-msg-required="<?= MSG_CHECKOUT_NAME_EMPTY ?>">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="email">Email *</label>
              <input type="email" id="email" name="email" class="form-control"
                value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '') ?>"
                placeholder="jean@exemple.fr" autocomplete="email"
                data-validate="required|email"
                data-msg-required="<?= MSG_CHECKOUT_EMAIL_EMPTY ?>"
                data-msg-email="<?= MSG_CHECKOUT_EMAIL_INVALID ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="phone">Téléphone *</label>
              <input type="tel" id="phone" name="phone" class="form-control"
                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                placeholder="06 12 34 56 78" autocomplete="tel"
                data-validate="required|phone"
                data-msg-required="<?= MSG_CHECKOUT_PHONE_EMPTY ?>"
                data-msg-phone="<?= MSG_CHECKOUT_PHONE_INVALID ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="address">Adresse de livraison *</label>
            <input type="text" id="address" name="address" class="form-control"
              value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
              placeholder="12 Rue des Fleurs" autocomplete="street-address"
              data-validate="required"
              data-msg-required="<?= MSG_CHECKOUT_ADDRESS_EMPTY ?>">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="city">Ville *</label>
              <input type="text" id="city" name="city" class="form-control"
                value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                placeholder="Paris" autocomplete="address-level2"
                data-validate="required"
                data-msg-required="<?= MSG_CHECKOUT_CITY_EMPTY ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="postal_code">Code postal *</label>
              <input type="text" id="postal_code" name="postal_code" class="form-control"
                value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>"
                placeholder="75001" maxlength="5" autocomplete="postal-code"
                data-validate="required|postal"
                data-msg-required="<?= MSG_CHECKOUT_POSTAL_EMPTY ?>"
                data-msg-postal="<?= MSG_CHECKOUT_POSTAL_INVALID ?>">
            </div>
          </div>

          <div style="background:var(--clr-surface);border:1px solid var(--clr-border);border-radius:var(--radius-md);padding:1.25rem;margin-bottom:1.5rem">
            <div style="display:flex;align-items:center;gap:.75rem">
              <svg width="22" height="22" fill="none" stroke="var(--clr-primary)" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <div>
                <div style="font-weight:600">Paiement à la livraison</div>
                <div style="font-size:.82rem;color:var(--clr-muted)">Payez en espèces ou par carte lors de la réception de votre commande.</div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-order">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Confirmer la commande
          </button>
        </form>
      </div>

      <!-- Résumé -->
      <div>
        <div class="order-summary" style="background:var(--clr-surface);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:1.75rem;position:sticky;top:calc(var(--nav-h)+1rem)">
          <h3 style="margin-bottom:1.25rem">Votre commande</h3>
          <p style="font-size:.85rem;color:var(--clr-muted);text-align:right">Livraison gratuite dès 100,000 TND</p>
          <div id="checkout-summary-items" style="font-size:.9rem;color:var(--clr-muted);margin-bottom:1rem"></div>
          <div style="border-top:1px solid var(--clr-border);padding-top:1rem">
            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.9rem"><span>Sous-total</span><span id="cs-subtotal">—</span></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.75rem;font-size:.9rem"><span>Livraison</span><span id="cs-shipping">—</span></div>
            <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.1rem;color:var(--clr-primary)"><span>Total</span><span id="cs-total">—</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.order-summary { background:var(--clr-surface); }
@media(max-width:768px) { .container > div[style*="grid"] { grid-template-columns:1fr !important; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const cart = JSON.parse(localStorage.getItem('vitanova_cart') || '[]');
  document.getElementById('cart-items-input').value = JSON.stringify(cart);

  const fmt = n => n.toFixed(3).replace('.', ',') + ' TND';
  const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
  const shipping = subtotal >= 100 ? 0 : 7.000;
  const total = subtotal + shipping;

  document.getElementById('checkout-summary-items').innerHTML =
    cart.map(i => `<div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--clr-border)">
      <span>${i.name} × ${i.quantity}</span><span style="font-weight:600">${fmt(i.price * i.quantity)}</span>
    </div>`).join('') || '<p>Panier vide</p>';

  document.getElementById('cs-subtotal').textContent = fmt(subtotal);
  document.getElementById('cs-shipping').textContent = shipping === 0 ? 'Gratuite' : fmt(shipping);
  document.getElementById('cs-total').textContent = fmt(total);

  if (!cart.length) {
    document.getElementById('submit-order').disabled = true;
    document.getElementById('checkout-summary-items').innerHTML = '<p style="color:var(--clr-error)">Votre panier est vide.</p>';
  }

  document.getElementById('checkout-form').addEventListener('submit', () => {
    document.getElementById('cart-items-input').value = JSON.stringify(cart);
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>
