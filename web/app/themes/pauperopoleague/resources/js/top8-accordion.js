document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('toggle', (e) => {
    const opened = e.target.closest('.deck-card');
    if (!opened || !opened.open) return;

    document.querySelectorAll('.deck-card[open]').forEach(card => {
      if (card !== opened) card.removeAttribute('open');
    });
  }, true);
});
