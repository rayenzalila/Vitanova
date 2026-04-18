/* Vitanova — Cart JS (localStorage + PHP session sync) */
'use strict';

const CART_KEY = 'vitanova_cart';

// ── Core helpers ───────────────────────────────────────
function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch { return []; }
}
function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

// ── CRUD ───────────────────────────────────────────────
function addToCart(product) {
  const cart = getCart();
  const idx  = cart.findIndex(i => i.id === product.id);
  if (idx > -1) {
    cart[idx].quantity = Math.min(cart[idx].quantity + (product.quantity || 1), product.stock || 99);
  } else {
    cart.push({ id: product.id, name: product.name, price: product.price,
                quantity: product.quantity || 1, image: product.image || '',
                category: product.category || '', slug: product.slug || '' });
  }
  saveCart(cart);
  syncCartToSession();
  if (window.showToast) showToast('Produit ajouté au panier avec succès.', 'success');
}

function removeFromCart(id) {
  saveCart(getCart().filter(i => i.id !== id));
  syncCartToSession();
  if (window.showToast) showToast('Produit retiré du panier.', 'success');
}

function updateQuantity(id, qty) {
  const cart = getCart();
  const idx  = cart.findIndex(i => i.id === id);
  if (idx === -1) return;
  if (qty <= 0) { removeFromCart(id); return; }
  cart[idx].quantity = qty;
  saveCart(cart);
  syncCartToSession();
  if (window.showToast) showToast('Panier mis à jour.', 'success');
}

function clearCart() {
  localStorage.removeItem(CART_KEY);
  updateCartBadge();
}

// ── Badge count ────────────────────────────────────────
function updateCartBadge() {
  const total = getCart().reduce((s, i) => s + i.quantity, 0);
  document.querySelectorAll('.cart-badge').forEach(b => {
    b.textContent = total;
    b.style.display = total > 0 ? 'flex' : 'none';
  });
}

// ── Sync to PHP session ────────────────────────────────
function syncCartToSession() {
  const base = document.querySelector('meta[name="base-url"]')?.content || '';
  fetch(`${base}/api/cart.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' },
    body: JSON.stringify({ action: 'sync', items: getCart() })
  }).catch(() => {}); // Silent fail — cart still works via localStorage
}

// ── Cart totals helper ─────────────────────────────────
function getCartTotal() {
  return getCart().reduce((s, i) => s + (i.price * i.quantity), 0);
}
function formatPrice(n) {
  return n.toFixed(3).replace('.', ',') + ' TND';
}

// ── Render cart page ───────────────────────────────────
function renderCartPage() {
  const container = document.getElementById('cart-items');
  const summary   = document.getElementById('cart-summary');
  if (!container) return;

  const cart = getCart();
  if (cart.length === 0) {
    container.innerHTML = `<div class="cart-empty">
      <svg width="64" height="64" fill="none" stroke="#5dcaa5" stroke-width="1.5" viewBox="0 0 24 24">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
      </svg>
      <p>Votre panier est vide. Découvrez nos produits !</p>
      <a href="boutique.php" class="btn btn-primary">Découvrir la boutique</a>
    </div>`;
    if (summary) summary.innerHTML = '';
    return;
  }

  container.innerHTML = cart.map(item => `
    <div class="cart-item" data-id="${item.id}">
      <div class="cart-item__img">${item.image
        ? `<img src="${item.image}" alt="${item.name}">`
        : `<div class="cart-item__placeholder">${item.name.charAt(0).toUpperCase()}</div>`}
      </div>
      <div class="cart-item__info">
        <h3>${item.name}</h3>
        <p class="cart-item__price">${formatPrice(item.price)}</p>
      </div>
      <div class="cart-item__qty">
        <button class="qty-btn qty-minus" data-id="${item.id}">−</button>
        <span class="qty-val">${item.quantity}</span>
        <button class="qty-btn qty-plus"  data-id="${item.id}">+</button>
      </div>
      <p class="cart-item__subtotal">${formatPrice(item.price * item.quantity)}</p>
      <button class="cart-item__remove" data-id="${item.id}" aria-label="Supprimer">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>
        </svg>
      </button>
    </div>`).join('');

  const subtotal = getCartTotal();
  const shipping = subtotal >= 100 ? 0 : 7.000;
  const total    = subtotal + shipping;

  if (summary) summary.innerHTML = `
    <div class="order-summary">
      <h3>Résumé de la commande</h3>
      <div class="summary-row"><span>Sous-total</span><span>${formatPrice(subtotal)}</span></div>
      <div class="summary-row"><span>Livraison</span>
        <span>${shipping === 0 ? '<span class="text-success">Gratuite</span>' : formatPrice(shipping)}</span></div>
      ${shipping > 0 ? `<p class="shipping-note">Livraison gratuite dès 100,000 TND</p>` : ''}
      <div class="summary-row summary-row--total"><span>Total</span><span>${formatPrice(total)}</span></div>
      <div class="payment-badge"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Paiement à la livraison</div>
      <a href="commander.php" class="btn btn-primary btn-block btn-lg">Passer la commande</a>
    </div>`;

  // Events
  container.querySelectorAll('.qty-minus').forEach(btn => btn.addEventListener('click', () => {
    const id = parseInt(btn.dataset.id);
    const item = getCart().find(i => i.id === id);
    if (item) updateQuantity(id, item.quantity - 1);
    renderCartPage();
  }));
  container.querySelectorAll('.qty-plus').forEach(btn => btn.addEventListener('click', () => {
    const id = parseInt(btn.dataset.id);
    const item = getCart().find(i => i.id === id);
    if (item) updateQuantity(id, item.quantity + 1);
    renderCartPage();
  }));
  container.querySelectorAll('.cart-item__remove').forEach(btn => btn.addEventListener('click', () => {
    removeFromCart(parseInt(btn.dataset.id));
    renderCartPage();
  }));
}

// ── Add-to-cart buttons on any page ───────────────────
function bindAddToCartButtons() {
  document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
    btn.addEventListener('click', () => {
      addToCart({
        id:       parseInt(btn.dataset.productId),
        name:     btn.dataset.productName,
        price:    parseFloat(btn.dataset.productPrice),
        image:    btn.dataset.productImage || '',
        category: btn.dataset.productCategory || '',
        slug:     btn.dataset.productSlug || '',
        quantity: parseInt(document.getElementById('qty-input')?.value || 1),
        stock:    parseInt(btn.dataset.productStock || 99)
      });
    });
  });
}

// ── Init ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();
  renderCartPage();
  bindAddToCartButtons();
  syncCartToSession();
});

window.CartVitanova = { addToCart, removeFromCart, updateQuantity, clearCart, getCart, getCartTotal };
