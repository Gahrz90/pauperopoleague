document.addEventListener('DOMContentLoaded', () => {

  // ─── Form iscrizione ────────────────────────────────────────────────────────

  const app = document.getElementById('decklist-app');
  if (app) {
    const tappaId = app.dataset.tappaId;
    const restUrl = app.dataset.restUrl;
    const nonce   = app.dataset.restNonce;

    const stepCodice   = document.getElementById('step-codice');
    const inputCodice  = document.getElementById('codice-tappa');
    const btnVerifica  = document.getElementById('btn-verifica-codice');
    const erroreCodice = document.getElementById('errore-codice');

    const stepForm       = document.getElementById('step-form');
    const inputGiocatore = document.getElementById('nome-giocatore');
    const inputArchetipo = document.getElementById('archetipo');
    const inputTitolo    = document.getElementById('titolo-mazzo');
    const btnInvia       = document.getElementById('btn-invia-decklist');
    const erroreForm     = document.getElementById('errore-form');
    const successoForm   = document.getElementById('successo-form');

    let codiceVerificato = null;

    const showError  = (el, msg) => { el.textContent = msg; el.hidden = false; };
    const hideError  = (el)      => { el.textContent = '';  el.hidden = true; };
    const setLoading = (btn, on) => {
      btn.disabled    = on;
      btn.textContent = on ? 'Attendere...' : btn.dataset.label;
    };

    btnVerifica.dataset.label = btnVerifica.textContent;
    btnInvia.dataset.label    = btnInvia.textContent;

    // ─── Deck builder ──────────────────────────────────────────────────────────

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
      } catch { return []; }
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
        if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(active + 1, items.length - 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(active - 1, 0); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); input.value = items[active].textContent; list.hidden = true; return; }
        else if (e.key === 'Escape') { list.hidden = true; return; }
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
      removeBtn.addEventListener('click', () => { row.remove(); refreshCounter(rowsEl, counterEl, max); });

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
      insertAfter ? insertAfter.after(row) : rowsEl.appendChild(row);
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

    const mainRowsEl    = document.getElementById('rows-mainboard');
    const mainCounterEl = document.getElementById('counter-mainboard');
    const sideRowsEl    = document.getElementById('rows-sideboard');
    const sideCounterEl = document.getElementById('counter-sideboard');

    addRow(mainRowsEl, mainCounterEl, MAIN_MAX);
    addRow(sideRowsEl, sideCounterEl, SIDE_MAX);

    // ─── Archetipo dropdown ────────────────────────────────────────────────────

    async function populateArchetypes() {
      try {
        const res  = await fetch(restUrl.replace('/decklist', '/metagame'));
        const list = await res.json();
        inputArchetipo.innerHTML = '<option value="" disabled selected>Seleziona archetipo...</option>';
        list.forEach((name) => {
          const opt       = document.createElement('option');
          opt.value       = name;
          opt.textContent = name;
          inputArchetipo.appendChild(opt);
        });
        const other       = document.createElement('option');
        other.value       = '__other__';
        other.textContent = 'Altro...';
        inputArchetipo.appendChild(other);
        inputArchetipo.disabled = false;

        const otherInput       = document.createElement('input');
        otherInput.type        = 'text';
        otherInput.id          = 'archetipo-altro';
        otherInput.placeholder = 'Specifica archetipo...';
        otherInput.hidden      = true;
        inputArchetipo.after(otherInput);

        inputArchetipo.addEventListener('change', () => {
          otherInput.hidden = inputArchetipo.value !== '__other__';
          if (!otherInput.hidden) otherInput.focus();
        });
      } catch {
        inputArchetipo.innerHTML = '<option value="">Errore caricamento</option>';
      }
    }

    // ─── Step 1 ────────────────────────────────────────────────────────────────

    btnVerifica.addEventListener('click', async () => {
      hideError(erroreCodice);
      const codice = inputCodice.value.trim();
      if (!codice) { showError(erroreCodice, 'Inserisci il codice tappa.'); return; }

      setLoading(btnVerifica, true);
      try {
        const res  = await fetch(restUrl.replace('/decklist', '/verify-code'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
          body: JSON.stringify({ tappa_id: tappaId, codice_tappa: codice }),
        });
        const data = await res.json();
        if (!res.ok) { showError(erroreCodice, data.message || 'Errore. Riprova.'); return; }
        codiceVerificato  = codice;
        stepCodice.hidden = true;
        stepForm.hidden   = false;
        populateArchetypes();
      } catch {
        showError(erroreCodice, 'Errore di rete. Riprova.');
      } finally {
        setLoading(btnVerifica, false);
      }
    });

    // ─── Step 2 ────────────────────────────────────────────────────────────────

    btnInvia.addEventListener('click', async () => {
      hideError(erroreForm);
      successoForm.hidden = true;

      const nomeGiocatore = inputGiocatore.value.trim();
      const otherInput    = document.getElementById('archetipo-altro');
      const archetipo     = inputArchetipo.value === '__other__'
        ? (otherInput ? otherInput.value.trim() : '')
        : inputArchetipo.value.trim();
      const titolo = inputTitolo.value.trim();
      const mazzo  = serializeMazzo();

      if (!nomeGiocatore || !archetipo || !titolo || !mazzo) {
        showError(erroreForm, 'Tutti i campi sono obbligatori.');
        return;
      }

      setLoading(btnInvia, true);
      try {
        const res  = await fetch(restUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
          body: JSON.stringify({ tappa_id: tappaId, codice_tappa: codiceVerificato, nome_giocatore: nomeGiocatore, archetipo, titolo, mazzo }),
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
  }

  // ─── Card hover preview ─────────────────────────────────────────────────────

  const cardNames = document.querySelectorAll('.card-name');
  if (cardNames.length) {
    const tooltip = document.createElement('div');
    tooltip.className = 'card-preview';
    tooltip.hidden    = true;
    document.body.appendChild(tooltip);

    const cache = new Map();

    async function getCardImage(name) {
      if (cache.has(name)) return cache.get(name);
      try {
        const res  = await fetch('https://api.scryfall.com/cards/named?fuzzy=' + encodeURIComponent(name));
        const data = await res.json();
        const url  = data.image_uris?.normal ?? data.card_faces?.[0]?.image_uris?.normal ?? null;
        cache.set(name, url);
        return url;
      } catch { cache.set(name, null); return null; }
    }

    const place = (e) => {
      tooltip.style.left = (e.pageX + 16) + 'px';
      tooltip.style.top  = (e.pageY + 16) + 'px';
    };

    cardNames.forEach((el) => {
      el.addEventListener('mouseenter', async (e) => {
        const url = await getCardImage(el.dataset.card);
        if (!url) return;
        tooltip.innerHTML = `<img src="${url}" alt="${el.dataset.card}" loading="lazy" />`;
        tooltip.hidden = false;
        place(e);
      });
      el.addEventListener('mousemove', place);
      el.addEventListener('mouseleave', () => { tooltip.hidden = true; });
    });
  }

  // ─── Metagame charts ────────────────────────────────────────────────────────

  const statsEl = document.getElementById('metagame-stats');
  if (statsEl) {
    const archetypeData = JSON.parse(statsEl.dataset.archetypes || '{}');
    const cardData      = JSON.parse(statsEl.dataset.cards      || '{}');

    function renderBars(containerId, data) {
      const container = document.getElementById(containerId);
      if (!container) return;
      const entries = Object.entries(data);
      const max     = entries[0]?.[1] || 1;
      container.innerHTML = entries.map(([label, val]) => `
        <div class="bar-row">
          <span class="bar-row__label">${label}</span>
          <div class="bar-row__track">
            <div class="bar-row__fill" style="width:${(val / max * 100).toFixed(1)}%"></div>
          </div>
          <span class="bar-row__value">${val}</span>
        </div>
      `).join('');
    }

    renderBars('chart-archetypes', archetypeData);
    renderBars('chart-cards', cardData);
  }

});
