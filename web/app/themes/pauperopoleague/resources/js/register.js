const cfg = window.pauperoRegister;
if (!cfg) throw new Error('pauperoRegister config missing');

const form        = document.getElementById('reg-form');
const successEl   = document.getElementById('reg-success');
const successEmail = document.getElementById('reg-success-email');
const errorEl     = document.getElementById('reg-error');
const submitBtn   = document.getElementById('reg-submit');

if (!form) throw new Error('Registration form not found');

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
  submitBtn.querySelector('.reg-submit__label').hidden = loading;
  submitBtn.querySelector('.reg-submit__spinner').hidden = !loading;
}

function validate(data) {
  const errors = {};

  if (!data.nome.trim())    errors.nome    = 'Il nome è obbligatorio.';
  if (!data.cognome.trim()) errors.cognome = 'Il cognome è obbligatorio.';

  if (!data.nome_utente.trim()) {
    errors.nome_utente = 'Il nome utente è obbligatorio.';
  } else if (!/^[a-zA-Z0-9_.-]+$/.test(data.nome_utente.trim())) {
    errors.nome_utente = 'Solo lettere, numeri, punti, trattini e underscore.';
  }

  if (!data.email.trim()) {
    errors.email = "L'email è obbligatoria.";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.email = "Inserisci un'email valida.";
  }

  if (!data.password) {
    errors.password = 'La password è obbligatoria.';
  } else if (data.password.length < 8) {
    errors.password = 'La password deve essere di almeno 8 caratteri.';
  }

  if (!data.confirm_password) {
    errors.confirm_password = 'Conferma la password.';
  } else if (data.password !== data.confirm_password) {
    errors.confirm_password = 'Le password non corrispondono.';
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
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce':   cfg.nonce,
      },
      body: JSON.stringify({
        nome:             data.nome,
        cognome:          data.cognome,
        email:            data.email,
        nome_utente:      data.nome_utente,
        password:         data.password,
        confirm_password: data.confirm_password,
        data_nascita:     data.data_nascita     || '',
        cellulare:        data.cellulare        || '',
        bio:              data.bio              || '',
        mazzi_giocati:    data.mazzi_giocati    || '',
      }),
    });

    const json = await res.json();

    if (!res.ok) {
      const code = json.code || '';
      if (code === 'password_mismatch' || code === 'password_too_short') {
        setFieldError('password', json.message);
        setFieldError('confirm_password', json.message);
      } else if (code === 'email_exists') {
        setFieldError('email', json.message);
      } else if (code === 'username_exists') {
        setFieldError('nome_utente', json.message);
      } else {
        errorEl.textContent = json.message || 'Si è verificato un errore. Riprova.';
        errorEl.hidden = false;
      }
      return;
    }

    // Success — swap form for confirmation panel
    form.hidden = true;
    successEmail.textContent = data.email;
    successEl.hidden = false;

  } catch {
    errorEl.textContent = 'Errore di rete. Controlla la connessione e riprova.';
    errorEl.hidden = false;
  } finally {
    setLoading(false);
  }
});

// Live password-match hint
const passwordInput = document.getElementById('reg-password');
const confirmInput  = document.getElementById('reg-confirm-password');

function liveMatchCheck() {
  if (confirmInput.value && passwordInput.value !== confirmInput.value) {
    setFieldError('confirm_password', 'Le password non corrispondono.');
  } else {
    setFieldError('confirm_password', '');
  }
}

passwordInput.addEventListener('input', liveMatchCheck);
confirmInput.addEventListener('input',  liveMatchCheck);
