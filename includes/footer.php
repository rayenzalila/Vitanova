<?php /** Vitanova — Footer partagé */ ?>
</main><!-- /main-content -->

<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer__grid">

      <div>
        <div class="footer__logo"><span>Vita</span><span class="nova">nova</span></div>
        <p class="footer__tagline">Des compléments alimentaires naturels et bio pour retrouver votre équilibre. Formulés avec soin, certifiés pour votre bien-être.</p>
        <div style="display:flex;gap:.75rem;margin-top:1.25rem">
          <a href="#" aria-label="Instagram" style="color:rgba(255,255,255,.7);transition:color .3s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.7)'">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/>
              <circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>
            </svg>
          </a>
          <a href="#" aria-label="Facebook" style="color:rgba(255,255,255,.7);transition:color .3s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.7)'">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
            </svg>
          </a>
        </div>
      </div>

      <div>
        <h4>Navigation</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
          <li><a href="<?= BASE_URL ?>/boutique.php">Boutique</a></li>
          <li><a href="<?= BASE_URL ?>/boutique.php?cat=stress">Stress & Anxiété</a></li>
          <li><a href="<?= BASE_URL ?>/boutique.php?cat=sommeil">Sommeil</a></li>
          <li><a href="<?= BASE_URL ?>/boutique.php?cat=energie">Énergie & Focus</a></li>
          <li><a href="<?= BASE_URL ?>/boutique.php?cat=bien-etre">Bien-être</a></li>
          <li><a href="<?= BASE_URL ?>/contact.php">Contact</a></li>
        </ul>
      </div>

      <div class="footer__contact">
        <h4>Contact</h4>
        <p>
          📍 12 Rue des Plantes Naturelles<br>
          75015 Paris, France<br><br>
          📧 <a href="mailto:contact@vitanova.fr" style="color:rgba(255,255,255,.8)">contact@vitanova.fr</a><br>
          📞 <a href="tel:+33123456789" style="color:rgba(255,255,255,.8)">+33 1 23 45 67 89</a><br><br>
          🕐 Lun–Ven : 9h–18h
        </p>
      </div>

    </div>

    <div class="footer__bottom">
      <p>© <?= date('Y') ?> Vitanova. Tous droits réservés. &nbsp;|&nbsp;
        <a href="#" style="color:rgba(255,255,255,.5)">Mentions légales</a> &nbsp;|&nbsp;
        <a href="#" style="color:rgba(255,255,255,.5)">Politique de confidentialité</a>
      </p>
    </div>
  </div>
</footer>

<div id="toast-container" role="region" aria-live="polite" aria-label="Notifications"></div>

<script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
