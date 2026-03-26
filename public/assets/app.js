document.addEventListener('DOMContentLoaded', () => {
  const ticker = document.querySelector('[data-ticker]');
  if (ticker) {
    let idx = 0;
    const items = Array.from(ticker.querySelectorAll('li'));
    if (items.length > 0) {
      items.forEach((el, i) => el.classList.toggle('hidden', i !== 0));
      setInterval(() => {
        items[idx].classList.add('hidden');
        idx = (idx + 1) % items.length;
        items[idx].classList.remove('hidden');
      }, 3000);
    }
  }

  document.querySelectorAll('[data-quick-view]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.dataset.quickView);
      if (modal) modal.classList.remove('hidden');
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.modal')?.classList.add('hidden'));
  });

  document.querySelectorAll('.tilt-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const rotateX = ((y / rect.height) - 0.5) * -8;
      const rotateY = ((x / rect.width) - 0.5) * 8;
      card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = 'perspective(800px) rotateX(0) rotateY(0)'; });
  });
});
