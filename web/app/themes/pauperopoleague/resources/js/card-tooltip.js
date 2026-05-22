const cache = {};
let tooltip = null;
let currentTarget = null;

// Scryfall "normal" image ratio: 488×680 → at 200px wide ≈ 279px tall
const TIP_W = 200;
const TIP_H = 279;

function getTooltip() {
  if (tooltip) return tooltip;

  tooltip = document.createElement('div');
  tooltip.id = 'card-preview-tooltip';
  Object.assign(tooltip.style, {
    position: 'fixed',
    zIndex: '99999',
    pointerEvents: 'none',
    display: 'none',
    borderRadius: '8px',
    overflow: 'hidden',
    boxShadow: '0 8px 32px rgba(0,0,0,0.7)',
    width: TIP_W + 'px',
  });

  const img = document.createElement('img');
  img.style.cssText = 'display:block;width:100%;height:auto;';
  tooltip.appendChild(img);
  document.body.appendChild(tooltip);
  return tooltip;
}

function centerTooltip() {
  const tip = getTooltip();
  tip.style.left = Math.round((window.innerWidth  - TIP_W) / 2) + 'px';
  tip.style.top  = Math.round((window.innerHeight - TIP_H) / 2) + 'px';
}

async function showCardPreview() {
  const el   = this;
  const name = el.dataset.card;
  if (!name) return;

  currentTarget = el;
  const tip = getTooltip();
  const img = tip.querySelector('img');

  centerTooltip();

  if (cache[name] === null) return;
  if (cache[name]) {
    img.src = cache[name];
    tip.style.display = 'block';
    return;
  }

  try {
    const url = `https://api.scryfall.com/cards/named?exact=${encodeURIComponent(name)}`;
    const res = await fetch(url);
    if (!res.ok) { cache[name] = null; return; }
    const data = await res.json();
    const imageUrl = data?.image_uris?.normal ?? data?.card_faces?.[0]?.image_uris?.normal;
    if (!imageUrl) { cache[name] = null; return; }
    cache[name] = imageUrl;

    if (currentTarget !== el) return;
    img.src = imageUrl;
    tip.style.display = 'block';
  } catch {
    cache[name] = null;
  }
}

function hideCardPreview() {
  currentTarget = null;
  if (tooltip) tooltip.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-card]').forEach(el => {
    el.addEventListener('mouseenter', showCardPreview);
    el.addEventListener('mouseleave', hideCardPreview);
  });
});
