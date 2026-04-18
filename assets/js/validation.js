/* Vitanova — Client-side form validation (French messages) */
'use strict';

// ── Validators ─────────────────────────────────────────
const validateEmail    = v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim());
const validatePhone    = v => /^(?:\+216|00216)?[234579]\d{7}$/.test(v.replace(/[\s\-.]/g, ''));
const validatePostal   = v => /^[1-9]\d{3}$/.test(v.replace(/[\s\-.]/g, ''));
const validateRequired = v => v.trim().length > 0;
const validateMinLen   = (v, n) => v.trim().length >= n;

// ── Error display ──────────────────────────────────────
function showFieldError(inputEl, message) {
  clearFieldError(inputEl);
  inputEl.classList.add('error');
  const span = document.createElement('span');
  span.className = 'field-error';
  span.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
  </svg> ${message}`;
  inputEl.parentNode.appendChild(span);
}
function clearFieldError(inputEl) {
  inputEl.classList.remove('error');
  const prev = inputEl.parentNode.querySelector('.field-error');
  if (prev) prev.remove();
}

// ── Blur listeners ─────────────────────────────────────
function attachBlurValidation(form) {
  form.querySelectorAll('[data-validate]').forEach(input => {
    input.addEventListener('blur', () => validateField(input));
    input.addEventListener('input', () => { if (input.classList.contains('error')) validateField(input); });
  });
}

function validateField(input) {
  const rules = (input.dataset.validate || '').split('|');
  clearFieldError(input);
  for (const rule of rules) {
    const [name, param] = rule.split(':');
    if (name === 'required' && !validateRequired(input.value)) {
      showFieldError(input, input.dataset.msgRequired || 'Ce champ est obligatoire.'); return false;
    }
    if (name === 'email' && input.value.trim() && !validateEmail(input.value)) {
      showFieldError(input, input.dataset.msgEmail || "L'adresse email saisie n'est pas valide."); return false;
    }
    if (name === 'phone' && input.value.trim() && !validatePhone(input.value)) {
      showFieldError(input, input.dataset.msgPhone || 'Veuillez saisir un numéro de téléphone tunisien valide (ex: 55 123 456).'); return false;
    }
    if (name === 'postal' && input.value.trim() && !validatePostal(input.value)) {
      showFieldError(input, input.dataset.msgPostal || 'Veuillez saisir un code postal tunisien valide (4 chiffres).'); return false;
    }
    if (name === 'min' && input.value.trim() && !validateMinLen(input.value, parseInt(param))) {
      showFieldError(input, input.dataset.msgMin || `Minimum ${param} caractères requis.`); return false;
    }
    if (name === 'match') {
      const target = document.getElementById(param);
      if (target && input.value !== target.value) {
        showFieldError(input, input.dataset.msgMatch || 'Les mots de passe ne correspondent pas.'); return false;
      }
    }
  }
  return true;
}

// ── Form-level validation ──────────────────────────────
function validateForm(form) {
  let valid = true;
  form.querySelectorAll('[data-validate]').forEach(input => {
    if (!validateField(input)) valid = false;
  });
  return valid;
}

// ── Bind all forms ─────────────────────────────────────
document.querySelectorAll('form[data-validate-form]').forEach(form => {
  attachBlurValidation(form);
  form.addEventListener('submit', e => {
    if (!validateForm(form)) e.preventDefault();
  });
});

window.VitanovaValidation = { validateForm, validateField, showFieldError, clearFieldError, validateEmail, validatePhone, validatePostal };

// ── Phone auto-formatting ──────────────────────────────
document.addEventListener('input', e => {
  if (e.target.tagName === 'INPUT' && (e.target.name === 'phone' || (e.target.dataset.validate || '').includes('phone'))) {
    let val = e.target.value;
    let oldLen = val.length;
    let cursor = e.target.selectionStart;
    
    let isPlus = val.startsWith('+');
    let digits = val.replace(/\D/g, '');
    let prefix = '';
    let local = digits;
    
    if (isPlus && digits.startsWith('216')) { prefix = '+216 '; local = digits.substring(3); }
    else if (digits.startsWith('00216')) { prefix = '00216 '; local = digits.substring(5); }
    
    local = local.substring(0, 8); // Max 8 digits for local part
    
    let match = local.match(/^(\d{0,2})(\d{0,3})(\d{0,3})$/);
    let formatted = '';
    if (match) {
        formatted = match[1];
        if (match[2]) formatted += ' ' + match[2];
        if (match[3]) formatted += ' ' + match[3];
    }
    
    let newVal = prefix + formatted;
    
    if (val !== newVal) {
        e.target.value = newVal;
        // Keep cursor at the end if user is typing at the end
        if (cursor === oldLen) {
             e.target.setSelectionRange(newVal.length, newVal.length);
        } else {
             // Basic cursor restoration for mid-string edits
             let addedSpaces = (newVal.match(/ /g) || []).length - (val.substring(0, cursor).match(/ /g) || []).length;
             let newCursor = Math.max(0, cursor + (addedSpaces > 0 ? 1 : 0));
             e.target.setSelectionRange(newCursor, newCursor);
        }
    }
  }
});
