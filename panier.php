<?php
$pageTitle = 'Mon Panier — Vitanova';
$pageDesc  = 'Votre panier Vitanova. Vérifiez vos articles et passez à la commande.';
require_once 'includes/header.php';
?>

<div class="page-header" style="text-align:center">
  <div class="container">
    <h1 style="font-size:clamp(1.5rem,5vw,2.2rem)">Mon Panier</h1>
    <p style="margin-top:.5rem;color:var(--clr-muted)">Vérifiez vos articles et passez à la commande.</p>
  </div>
</div>

<section class="section" style="background:var(--clr-bg)">
  <div class="container">
    <div id="cart-content-wrapper" class="grid-responsive-2" style="grid-template-columns:1fr 360px;align-items:start">
      <div id="cart-items">
        <!-- Rendered by cart.js -->
        <div style="text-align:center;padding:4rem 1rem;color:var(--clr-muted)">
          <div class="spinner spinner--green" style="margin:0 auto 1rem;width:32px;height:32px;border-width:3px"></div>
          <p>Chargement du panier...</p>
        </div>
      </div>
      <div id="cart-summary"></div>
    </div>
  </div>
</section>

<style>
.cart-empty { text-align:center; padding:4rem 1rem; }
.cart-empty svg { margin:0 auto 1.5rem; }
.cart-empty p { color:var(--clr-muted); margin-bottom:1.5rem; font-size:1.05rem; }
.cart-item { display:grid; grid-template-columns:80px 1fr auto auto auto; gap:1.25rem; align-items:center; padding:1.25rem; border:1px solid var(--clr-border); border-radius:var(--radius-md); margin-bottom:1rem; background:var(--clr-surface); }
.cart-item__img { width:80px; height:80px; border-radius:var(--radius-sm); overflow:hidden; background:var(--clr-border); }
.cart-item__img img, .cart-item__img svg { width:100%; height:100%; object-fit:cover; }
.cart-item__placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--clr-primary); color:#fff; font-weight:800; font-size:1.5rem; }
.cart-item__info h3 { font-size:1rem; margin-bottom:.25rem; }
.cart-item__price { color:var(--clr-muted); font-size:.9rem; }
.cart-item__qty { display:flex; align-items:center; gap:.5rem; }
.qty-btn { width:30px; height:30px; border:1.5px solid var(--clr-border); background:var(--clr-bg); border-radius:6px; cursor:pointer; font-size:1rem; font-weight:700; color:var(--clr-primary); display:flex; align-items:center; justify-content:center; transition:all .2s; }
.qty-btn:hover { background:var(--clr-primary); color:#fff; border-color:var(--clr-primary); }
.qty-val { font-weight:700; min-width:24px; text-align:center; }
.cart-item__subtotal { font-weight:700; color:var(--clr-primary); font-size:1rem; }
.cart-item__remove { background:none; border:none; cursor:pointer; color:var(--clr-muted); padding:.25rem; border-radius:6px; transition:color .2s; }
.cart-item__remove:hover { color:var(--clr-error); }
.order-summary { background:var(--clr-surface); border:1px solid var(--clr-border); border-radius:var(--radius-lg); padding:1.75rem; position:sticky; top:calc(var(--nav-h) + 1rem); }
.order-summary h3 { margin-bottom:1.25rem; font-size:1.1rem; }
.summary-row { display:flex; justify-content:space-between; align-items:center; padding:.6rem 0; font-size:.95rem; color:var(--clr-muted); border-bottom:1px solid var(--clr-border); }
.summary-row--total { font-weight:800; font-size:1.1rem; color:var(--clr-text); border-bottom:none; margin-top:.5rem; }
.shipping-note { font-size:.78rem; color:var(--clr-accent); margin-top:.25rem; }
.payment-badge { display:flex; align-items:center; gap:.5rem; font-size:.82rem; color:var(--clr-muted); padding:.75rem; background:var(--clr-bg); border:1px solid var(--clr-border); border-radius:var(--radius-sm); margin:1rem 0; }
.text-success { color:var(--clr-success); }
/* Handle empty cart centering */
#cart-items:only-child, .cart-empty { grid-column: 1 / -1; width: 100%; max-width: 600px; margin: 0 auto; }
#cart-content-wrapper:has(.cart-empty) { grid-template-columns: 1fr !important; }

@media(max-width:992px) {
  #main-content .container > .grid-responsive-2 { grid-template-columns: 1fr !important; }
  .order-summary { position: static; }
}
@media(max-width:768px) {
  .cart-item { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
