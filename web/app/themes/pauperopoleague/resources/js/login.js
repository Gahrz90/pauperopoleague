const cfg = window.pauperoLogin;
if (!cfg) throw new Error('pauperoLogin config missing');

const form      = document.getElementById('login-form');
const errorEl   = document.getElementById('login-error');
const submitBtn = document.getElementById('login-submit');

if (!form) throw new Error('Login form not found');

function setFieldError(name, msg) {
  const hint  = form.querySelector(`.form-error-msg[data-for="${name}"]`);
  const input = form.querySelector(`[name="${name}"]`);
  if (hint)  hint.textContent = msg;
  if (input) input.classList.toggle('is-invalid', !!msg);
}

function clearErrors() {
  form.querySelectorAll('.form-error-msg').forEach(el => (el.textContent = ''));
  form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  errorEl.hidden = true;
  errorEl.textContent = '';
}

function setLoading(loading) {
  submitBtn.disabled = loading;
  submitBtn.querySelector('.login-submit__label').hidden = loading;
  submitBtn.querySelector('.reg-submit__spinner').hidden = !loading;
}

function validate(data) {
  const errors = {};

  if (!data.email.trim()) {
    errors.email = "L'email è obbligatoria.";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.email = "Inserisci un'email valida.";
  }

  if (!data.password) {
    errors.password = 'La password è obbligatoria.';
  }

  return errors;
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  clearErrors();

  const fd   = new FormData(form);
  const data = Object.fromEntries(fd.entries());

  const errors = validate(data);
  if (Object.keys(errors).length) {
    Object.entries(errors).forEach(([field, msg]) => setFieldError(field, msg));
    form.querySelector('.is-invalid')?.focus();
    return;
  }

  setLoading(true);

  try {
    const res = await fetch(cfg.apiUrl, {
      method:      'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce':   cfg.nonce,
      },
      body: JSON.stringify({
        email:    data.email,
        password: data.password,
        remember: data.remember === '1',
      }),
    });

    const json = await res.json();

    if (!res.ok) {
      if (json.code === 'email_not_verified') {
        errorEl.innerHTML = json.message + ' <a href="' + (cfg.registerUrl || '/registrazione/') + '" style="color:inherit;font-weight:600;">Reinvia email</a>';
      } else {
        errorEl.textContent = json.message || 'Email o password non corretti.';
      }
      errorEl.hidden = false;
      return;
    }

    window.location.href = json.redirect_url || cfg.redirectTo;

  } catch {
    errorEl.textContent = 'Errore di rete. Controlla la connessione e riprova.';
    errorEl.hidden = false;
  } finally {
    setLoading(false);
  }
});
