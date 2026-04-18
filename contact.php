<?php
$pageTitle = 'Contact — Vitanova';
$pageDesc  = 'Contactez l\'équipe Vitanova pour toute question sur nos produits ou votre commande.';
require_once 'includes/header.php';
$success = false; $errors = [];

?>
<!-- EmailJS Script -->
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
<script>
  (function() {
      emailjs.init("LOLfmMNhmcBqfVMYv");
  })();
</script>
<div class="page-header"><div class="container"><h1>Contactez-nous</h1><p style="color:var(--clr-muted);margin-top:.5rem">Notre équipe vous répond sous 24h.</p></div></div>

<section class="section-sm" style="background:#fff">
<div class="container" style="max-width:960px">
  <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:3rem">

    <div class="card">
      <h2 style="font-size:1.2rem;margin-bottom:1.5rem">Envoyer un message</h2>
      <form id="contact-form" data-validate-form>
        <?= csrfField() ?>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="c-name">Nom *</label>
            <input type="text" id="c-name" name="name" class="form-control" placeholder="Votre nom"
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
              data-validate="required" data-msg-required="<?= MSG_CONTACT_NAME_EMPTY ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="c-email">Email *</label>
            <input type="email" id="c-email" name="email" class="form-control" placeholder="votre@email.fr"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              data-validate="required|email" data-msg-required="<?= MSG_CONTACT_EMAIL_EMPTY ?>" data-msg-email="<?= MSG_CONTACT_EMAIL_INVALID ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="c-subject">Sujet *</label>
          <input type="text" id="c-subject" name="subject" class="form-control" placeholder="Objet de votre message"
            value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
            data-validate="required" data-msg-required="<?= MSG_CONTACT_SUBJECT_EMPTY ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="c-message">Message *</label>
          <textarea id="c-message" name="message" rows="6" class="form-control" placeholder="Décrivez votre demande en détail..."
            data-validate="required|min:20"
            data-msg-required="<?= MSG_CONTACT_MESSAGE_EMPTY ?>"
            data-msg-min="<?= MSG_CONTACT_MESSAGE_SHORT ?>"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Envoyer le message</button>
      </form>
      </form>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <?php foreach([
        ['📍','Adresse','12 Rue des Plantes Naturelles<br>75015 Paris, France'],
        ['📧','Email','<a href="mailto:rayenzalila@gmail.com" style="color:var(--clr-primary)">rayenzalila@gmail.com</a>'],
        ['📞','Téléphone','<a href="tel:+33123456789" style="color:var(--clr-primary)">+33 1 23 45 67 89</a>'],
        ['🕐','Horaires','Lun – Ven : 9h – 18h<br>Sam – Dim : Fermé'],
      ] as $info): ?>
      <div class="card" style="display:flex;gap:1rem;align-items:flex-start">
        <span style="font-size:1.5rem"><?= $info[0] ?></span>
        <div>
          <h4 style="font-size:.85rem;color:var(--clr-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem"><?= $info[1] ?></h4>
          <p style="font-size:.9rem;line-height:1.6;margin:0"><?= $info[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('contact-form');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (window.VitanovaValidation && !window.VitanovaValidation.validateForm(form)) return;

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Envoi en cours...';
      submitBtn.disabled = true;

      try {
        const formData = new FormData(form);
        const response = await fetch('api/contact.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.success) {
          // Send via EmailJS after successful DB save
          try {
            await emailjs.send('service_evkb6oa', 'template_w3d7h84', {
              from_name: formData.get('name'),
              from_email: formData.get('email'),
              subject: formData.get('subject'),
              message: formData.get('message')
            });
          } catch (emailErr) {
            console.error('EmailJS Error:', emailErr);
          }

          if (window.showToast) {
            window.showToast(result.message || 'Message envoyé avec succès ! Nous vous répondrons sous 24h.', 'success');
          } else {
            alert(result.message || 'Message envoyé avec succès !');
          }
          form.reset();
        } else {
          if (window.showToast) {
            window.showToast(result.message || 'Erreur lors de l\'envoi.', 'error');
          } else {
            alert(result.message || 'Erreur lors de l\'envoi.');
          }
        }
      } catch (err) {
        console.error(err);
        if (window.showToast) {
          window.showToast('Une erreur est survenue.', 'error');
        } else {
          alert('Une erreur est survenue.');
        }
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  }
});
</script>
