document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    lucide.createIcons();
  }

  const searchInput = document.getElementById('dashboard-search');
  const cards = [...document.querySelectorAll('.dashboard-info-card')];

  if (!searchInput) return;

  searchInput.addEventListener('input', (event) => {
    const query = event.target.value.trim().toLowerCase();
    cards.forEach((card) => {
      card.hidden = Boolean(query) && !card.textContent.toLowerCase().includes(query);
    });
  });
});