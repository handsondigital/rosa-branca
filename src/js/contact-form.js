/**
 * Front-end only for now: client-side validation + visual states (default /
 * error / focus, matching the Figma Input component states). The actual
 * submit handler is intentionally not wired to a backend yet — pending a
 * decision on where submissions should go (see functions.php note).
 */
export function initContactForm() {
  const root = document.querySelector('[data-contact-form]');
  if (!root) return;

  const form = root.querySelector('form');
  const status = root.querySelector('[data-contact-form-status]');

  const validators = {
    email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
  };

  function validateField(field) {
    const control = field.querySelector('.field__control');
    const value = control.value.trim();
    let valid = control.checkValidity();

    if (valid && control.type === 'email') {
      valid = validators.email(value);
    }

    field.classList.toggle('field--invalid', !valid);
    control.setAttribute('aria-invalid', String(!valid));
    return valid;
  }

  form.querySelectorAll('[data-field]').forEach((field) => {
    const control = field.querySelector('.field__control');
    control.addEventListener('blur', () => validateField(field));
    control.addEventListener('input', () => {
      if (field.classList.contains('field--invalid')) validateField(field);
    });
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    const fields = Array.from(form.querySelectorAll('[data-field]'));
    const allValid = fields.map(validateField).every(Boolean);

    if (!allValid) {
      status.hidden = false;
      status.dataset.state = 'error';
      status.textContent = 'Verifique os campos destacados e tente novamente.';
      return;
    }

    // TODO: substituir por envio real (REST/admin-post) quando o destino
    // dos dados for definido.
    status.hidden = false;
    status.dataset.state = 'success';
    status.textContent = 'Mensagem pronta para envio — integração de envio ainda pendente.';
    form.reset();
  });
}
