/* Vitanova — Main JS: navbar, parallax, scroll reveal, toast, mobile drawer */
'use strict';

// ── Toast ──────────────────────────────────────────────
function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  const icon = type === 'success'
    ? '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>'
    : '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
  toast.innerHTML = icon + `<span>${message}</span>`;
  container.appendChild(toast);
  requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('show'); }); });
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}
window.showToast = showToast;

// ── Navbar scroll ──────────────────────────────────────
const navbar = document.querySelector('.navbar');
if (navbar) {
  const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 60);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

// ── Parallax hero ──────────────────────────────────────
const parallaxBg = document.querySelector('.parallax-bg');
if (parallaxBg) {
  window.addEventListener('scroll', () => {
    parallaxBg.style.transform = `translateY(${window.scrollY * 0.3}px)`;
  }, { passive: true });
}

// ── Scroll reveal ──────────────────────────────────────
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); revealObserver.unobserve(e.target); } });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ── Mobile drawer ──────────────────────────────────────
const hamburger = document.querySelector('.hamburger');
const drawer    = document.querySelector('.drawer');
const overlay   = document.querySelector('.drawer__overlay');
const closeBtn  = document.querySelector('.drawer__close');

function openDrawer()  { drawer?.classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeDrawer() { drawer?.classList.remove('open'); document.body.style.overflow = ''; }

hamburger?.addEventListener('click', openDrawer);
overlay?.addEventListener('click', closeDrawer);
closeBtn?.addEventListener('click', closeDrawer);
document.querySelectorAll('.drawer__links a').forEach(a => a.addEventListener('click', closeDrawer));

// ── Active nav link ────────────────────────────────────
const currentPath = window.location.pathname.split('/').pop() || 'index.php';
document.querySelectorAll('.navbar__links a, .drawer__links a').forEach(a => {
  const href = a.getAttribute('href')?.split('/').pop() || '';
  if (href === currentPath || (currentPath === '' && href === 'index.php')) a.classList.add('active');
});

// ── Tabs ───────────────────────────────────────────────
document.querySelectorAll('.tabs').forEach(tabGroup => {
  const buttons = tabGroup.querySelectorAll('.tab-btn');
  const panels  = tabGroup.querySelectorAll('.tab-content');
  buttons.forEach((btn, i) => {
    btn.addEventListener('click', () => {
      buttons.forEach(b => b.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      panels[i]?.classList.add('active');
    });
  });
  if (buttons.length) { buttons[0].classList.add('active'); panels[0]?.classList.add('active'); }
});

// ── Flash messages from PHP ────────────────────────────
const flashEl = document.getElementById('php-flash');
if (flashEl) {
  const { message, type } = flashEl.dataset;
  if (message) showToast(message, type || 'success');
}

// ── Interactive Star Rating ────────────────────────────
document.querySelectorAll('.stars[data-interactive="true"]').forEach(starsContainer => {
  const labels = Array.from(starsContainer.querySelectorAll('.star-label'));
  
  const updateStars = (hoverIndex = -1) => {
    let selectedIndex = -1;
    labels.forEach((l, i) => { if (l.querySelector('input').checked) selectedIndex = i; });
    
    const activeIndex = hoverIndex !== -1 ? hoverIndex : selectedIndex;
    
    labels.forEach((l, i) => {
      const poly = l.querySelector('polygon');
      if (poly) {
        poly.setAttribute('fill', i <= activeIndex ? '#f59e0b' : '#e5e7eb');
        // Add CSS classes just in case
        if (i <= activeIndex) {
            l.classList.add('active');
        } else {
            l.classList.remove('active');
        }
      }
    });
  };

  labels.forEach((label, index) => {
    label.addEventListener('mouseenter', () => updateStars(index));
    label.querySelector('input').addEventListener('change', () => updateStars());
  });
  
  starsContainer.addEventListener('mouseleave', () => updateStars());
  // Initial state setup
  updateStars();
});
