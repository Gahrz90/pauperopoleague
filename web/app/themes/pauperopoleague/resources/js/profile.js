const cfg = window.pauperoProfile;
if (!cfg) throw new Error('pauperoProfile config missing');

// ── Theme selector ─────────────────────────────────

const themeSelect = document.getElementById('prof-tema');

if (themeSelect) {
  const lsTheme = localStorage.getItem('paupero_theme');
  if (lsTheme && themeSelect.value !== lsTheme) {
    themeSelect.value = lsTheme;
  }

  themeSelect.addEventListener('change', async () => {
    const theme = themeSelect.value;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('paupero_theme', theme);

    try {
      await fetch(cfg.apiUrl, {
        method:      'POST',
        credentials: 'same-origin',
        headers:     { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
        body:        JSON.stringify({ theme }),
      });
    } catch {
      // theme already applied locally; server save is best-effort
    }
  });
}

function setFieldError(form, name, msg) {
  const hint  = form.querySelector(`.form-error-msg[data-for="${name}"]`);
  const input = form.querySelector(`[name="${name}"]`);
  if (hint)  hint.textContent = msg;
  if (input) input.classList.toggle('is-invalid', !!msg);
}

function clearFieldErrors(form) {
  form.querySelectorAll('.form-error-msg').forEach(el => (el.textContent = ''));
  form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

// ── Profile data form ─────────────────────────────

const profileForm    = document.getElementById('profile-form');
const profileSuccess = document.getElementById('profile-success');
const profileError   = document.getElementById('profile-error');
const profSubmit     = document.getElementById('prof-submit');

if (profileForm) {
  profileForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    profileSuccess.hidden = true;
    profileError.hidden   = true;

    const fd   = new FormData(profileForm);
    const data = {
      nome:          fd.get('nome')          ?? '',
      cognome:       fd.get('cognome')       ?? '',
      bio:           fd.get('bio')           ?? '',
      mazzi_giocati: fd.get('mazzi_giocati') ?? '',
      data_nascita:  fd.get('data_nascita')  ?? '',
      cellulare:     fd.get('cellulare')     ?? '',
    };

    profSubmit.disabled = true;
    profSubmit.querySelector('.prof-submit__label').hidden = true;
    profSubmit.querySelector('.reg-submit__spinner').hidden = false;

    try {
      const res  = await fetch(cfg.apiUrl, {
        method:      'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
        body: JSON.stringify(data),
      });
      const json = await res.json();

      if (!res.ok) {
        profileError.textContent = json.message || 'Errore durante il salvataggio.';
        profileError.hidden = false;
      } else {
        profileSuccess.textContent = json.message || 'Profilo aggiornato con successo.';
        profileSuccess.hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    } catch {
      profileError.textContent = 'Errore di rete. Controlla la connessione e riprova.';
      profileError.hidden = false;
    } finally {
      profSubmit.disabled = false;
      profSubmit.querySelector('.prof-submit__label').hidden = false;
      profSubmit.querySelector('.reg-submit__spinner').hidden = true;
    }
  });
}

// ── Password form ─────────────────────────────────

const pwForm   = document.getElementById('profile-pw-form');
const pwSuccess = document.getElementById('pw-success');
const pwError   = document.getElementById('pw-error');
const pwSubmit  = document.getElementById('pw-submit');

if (pwForm) {
  pwForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearFieldErrors(pwForm);
    pwSuccess.hidden = true;
    pwError.hidden   = true;

    const fd   = new FormData(pwForm);
    const data = Object.fromEntries(fd.entries());

    const errors = {};
    if (!data.current_password) {
      errors.current_password = 'Inserisci la password attuale.';
    }
    if (!data.new_password) {
      errors.new_password = 'Inserisci la nuova password.';
    } else if (data.new_password.length < 8) {
      errors.new_password = 'La password deve essere di almeno 8 caratteri.';
    }
    if (data.new_password !== data.confirm_new_password) {
      errors.confirm_new_password = 'Le password non corrispondono.';
    }

    if (Object.keys(errors).length) {
      Object.entries(errors).forEach(([field, msg]) => setFieldError(pwForm, field, msg));
      return;
    }

    pwSubmit.disabled = true;
    pwSubmit.querySelector('.pw-submit__label').hidden = true;
    pwSubmit.querySelector('.reg-submit__spinner').hidden = false;

    try {
      const res  = await fetch(cfg.passwordUrl, {
        method:      'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
        body: JSON.stringify(data),
      });
      const json = await res.json();

      if (!res.ok) {
        if (json.code === 'wrong_password') {
          setFieldError(pwForm, 'current_password', json.message);
        } else {
          pwError.textContent = json.message || 'Errore durante il cambio password.';
          pwError.hidden = false;
        }
      } else {
        pwSuccess.textContent = json.message || 'Password aggiornata con successo.';
        pwSuccess.hidden = false;
        pwForm.reset();
      }
    } catch {
      pwError.textContent = 'Errore di rete. Controlla la connessione e riprova.';
      pwError.hidden = false;
    } finally {
      pwSubmit.disabled = false;
      pwSubmit.querySelector('.pw-submit__label').hidden = false;
      pwSubmit.querySelector('.reg-submit__spinner').hidden = true;
    }
  });
}
