document.addEventListener('DOMContentLoaded', () => {
  const app = document.getElementById('decklist-app');
  if (!app) return;

  const tappaId = app.dataset.tappaId;
  const restUrl = app.dataset.restUrl;
  const nonce   = app.dataset.restNonce;

  // Step 1
  const stepCodice   = document.getElementById('step-codice');
  const inputCodice  = document.getElementById('codice-tappa');
  const btnVerifica  = document.getElementById('btn-verifica-codice');
  const erroreCodice = document.getElementById('errore-codice');

  // Step 2
  const stepForm       = document.getElementById('step-form');
  const inputGiocatore = document.getElementById('nome-giocatore');
  const inputArchetipo = document.getElementById('archetipo');
  const inputTitolo    = document.getElementById('titolo-mazzo');
  const btnInvia       = document.getElementById('btn-invia-decklist');
  const erroreForm     = document.getElementById('errore-form');
  const successoForm   = document.getElementById('successo-form');

  let codiceVerificato = null;

  // ─── Helpers ────────────────────────────────────────────────────────────────

  const showError  = (el, msg) => { el.textContent = msg; el.hidden = false; };
  const hideError  = (el)      => { el.textContent = '';  el.hidden = true; };
  const setLoading = (btn, on) => {
    btn.disabled    = on;
    btn.textContent = on ? 'Attendere...' : btn.dataset.label;
  };

  btnVerifica.dataset.label = btnVerifica.textContent;
  btnInvia.dataset.label    = btnInvia.textContent;

  // ─── Deck builder ───────────────────────────────────────────────────────────

  const MAIN_MAX = 60;
  const SIDE_MAX = 15;

  function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
  }

  async function fetchSuggestions(query) {
    if (query.length < 2) return [];
    try {
      const res  = await fetch('https://api.scryfall.com/cards/autocomplete?q=' + encodeURIComponent(query));
      const data = await res.json();
      return data.data || [];
    } catch {
      return [];
    }
  }

  function attachAutocomplete(input) {
    const wrap = input.closest('.decklist__cardname-wrap');
    const list = document.createElement('ul');
    list.className = 'decklist__suggestions';
    list.hidden    = true;
    wrap.appendChild(list);

    let active = -1;

    const render = (items) => {
      list.innerHTML = '';
      active = -1;
      if (!items.length) { list.hidden = true; return; }
      items.slice(0, 8).forEach((name) => {
        const li = document.createElement('li');
        li.textContent = name;
        li.addEventListener('mousedown', (e) => {
          e.preventDefault();
          input.value = name;
          list.hidden = true;
        });
        list.appendChild(li);
      });
      list.hidden = false;
    };

    const debouncedFetch = debounce(async (val) => render(await fetchSuggestions(val)), 300);

    input.addEventListener('input', () => debouncedFetch(input.value));

    input.addEventListener('keydown', (e) => {
      const items = [...list.querySelectorAll('li')];
      if (!items.length || list.hidden) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        active = Math.min(active + 1, items.length - 1);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        active = Math.max(active - 1, 0);
      } else if (e.key === 'Enter' && active >= 0) {
        e.preventDefault();
        input.value = items[active].textContent;
        list.hidden = true;
        return;
      } else if (e.key === 'Escape') {
        list.hidden = true;
        return;
      }
      items.forEach((li, i) => li.classList.toggle('is-active', i === active));
    });

    input.addEventListener('blur', () => setTimeout(() => { list.hidden = true; }, 150));
  }

  function sumQty(rowsEl) {
    return [...rowsEl.querySelectorAll('.decklist__qty')]
      .reduce((s, el) => s + (parseInt(el.value) || 0), 0);
  }

  function refreshCounter(rowsEl, counterEl, max) {
    counterEl.textContent = max - sumQty(rowsEl);
  }

  function addRow(rowsEl, counterEl, max, insertAfter = null) {
    const row = document.createElement('div');
    row.className = 'decklist__row';

    const qty = document.createElement('input');
    qty.type      = 'number';
    qty.min       = '1';
    qty.max       = '4';
    qty.value     = '1';
    qty.className = 'decklist__qty';
    qty.addEventListener('change', () => refreshCounter(rowsEl, counterEl, max));

    const nameWrap = document.createElement('div');
    nameWrap.className = 'decklist__cardname-wrap';

    const name = document.createElement('input');
    name.type         = 'text';
    name.className    = 'decklist__cardname';
    name.placeholder  = 'Nome carta...';
    name.autocomplete = 'off';
    nameWrap.appendChild(name);
    attachAutocomplete(name);

    const removeBtn = document.createElement('button');
    removeBtn.type        = 'button';
    removeBtn.className   = 'btn--row-action btn--row-remove';
    removeBtn.textContent = '×';
    removeBtn.setAttribute('aria-label', 'Rimuovi');
    removeBtn.addEventListener('click', () => {
      row.remove();
      refreshCounter(rowsEl, counterEl, max);
    });

    const plusBtn = document.createElement('button');
    plusBtn.type        = 'button';
    plusBtn.className   = 'btn--row-action btn--row-add';
    plusBtn.textContent = '+';
    plusBtn.setAttribute('aria-label', 'Aggiungi riga');
    plusBtn.addEventListener('click', () => {
      const newRow = addRow(rowsEl, counterEl, max, row);
      newRow.querySelector('.decklist__cardname').focus();
    });

    row.append(qty, nameWrap, removeBtn, plusBtn);

    if (insertAfter) {
      insertAfter.after(row);
    } else {
      rowsEl.appendChild(row);
    }

    refreshCounter(rowsEl, counterEl, max);
    return row;
  }

  function serializeMazzo() {
    const lines = (rowsEl) =>
      [...rowsEl.querySelectorAll('.decklist__row')]
        .map(r => {
          const qty  = r.querySelector('.decklist__qty').value;
          const name = r.querySelector('.decklist__cardname').value.trim();
          return name ? `${qty} ${name}` : null;
        })
        .filter(Boolean);

    const main = lines(mainRowsEl).join('\n');
    const side = lines(sideRowsEl).join('\n');
    return side ? `${main}\n\nSideboard:\n${side}` : main;
  }

  // Init columns
  const mainRowsEl    = document.getElementById('rows-mainboard');
  const mainCounterEl = document.getElementById('counter-mainboard');
  const sideRowsEl    = document.getElementById('rows-sideboard');
  const sideCounterEl = document.getElementById('counter-sideboard');

  if (mainRowsEl) {
    addRow(mainRowsEl, mainCounterEl, MAIN_MAX);
    addRow(sideRowsEl, sideCounterEl, SIDE_MAX);
  }

  // ─── Step 1: Verifica codice ────────────────────────────────────────────────

  btnVerifica.addEventListener('click', async () => {
    hideError(erroreCodice);
    const codice = inputCodice.value.trim();
    if (!codice) { showError(erroreCodice, 'Inserisci il codice tappa.'); return; }

    setLoading(btnVerifica, true);
    try {
      const res  = await fetch(restUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body: JSON.stringify({
          tappa_id: tappaId, codice_tappa: codice,
          nome_giocatore: '__verifica__', archetipo: '__verifica__',
          titolo: '__verifica__', mazzo: '__verifica__',
        }),
      });
      const data = await res.json();
      if (!res.ok) { showError(erroreCodice, data.message || 'Errore. Riprova.'); return; }
      codiceVerificato  = codice;
      stepCodice.hidden = true;
      stepForm.hidden   = false;
    } catch {
      showError(erroreCodice, 'Errore di rete. Riprova.');
    } finally {
      setLoading(btnVerifica, false);
    }
  });

  // ─── Step 2: Invio decklist ─────────────────────────────────────────────────

  btnInvia.addEventListener('click', async () => {
    hideError(erroreForm);
    successoForm.hidden = true;

    const nomeGiocatore = inputGiocatore.value.trim();
    const archetipo     = inputArchetipo.value.trim();
    const titolo        = inputTitolo.value.trim();
    const mazzo         = serializeMazzo();

    if (!nomeGiocatore || !archetipo || !titolo || !mazzo) {
      showError(erroreForm, 'Tutti i campi sono obbligatori.');
      return;
    }

    setLoading(btnInvia, true);
    try {
      const res  = await fetch(restUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body: JSON.stringify({
          tappa_id: tappaId, codice_tappa: codiceVerificato,
          nome_giocatore: nomeGiocatore, archetipo, titolo, mazzo,
        }),
      });
      const data = await res.json();
      if (!res.ok) { showError(erroreForm, data.message || 'Errore durante l\'invio.'); return; }
      stepForm.hidden          = true;
      successoForm.textContent = data.message;
      successoForm.hidden      = false;
    } catch {
      showError(erroreForm, 'Errore di rete. Riprova.');
    } finally {
      setLoading(btnInvia, false);
    }
  });
});
