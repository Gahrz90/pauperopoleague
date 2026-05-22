import './header/toggle-menu';
import './card-tooltip';

import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Countdown timer for homepage next-event card
document.addEventListener('DOMContentLoaded', () => {
  const el = document.querySelector('.hp-countdown[data-event-date]');
  if (!el) return;

  const target = new Date(el.dataset.eventDate).getTime();
  const pad = n => String(n).padStart(2, '0');

  function tick() {
    const diff = target - Date.now();
    const days    = Math.max(0, Math.floor(diff / 86400000));
    const hours   = Math.max(0, Math.floor((diff % 86400000) / 3600000));
    const minutes = Math.max(0, Math.floor((diff % 3600000) / 60000));

    el.querySelector('[data-unit="days"]').textContent    = pad(days);
    el.querySelector('[data-unit="hours"]').textContent   = pad(hours);
    el.querySelector('[data-unit="minutes"]').textContent = pad(minutes);
  }

  tick();
  setInterval(tick, 60000);
});
