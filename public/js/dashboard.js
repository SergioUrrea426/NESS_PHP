document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('dashboard-search');
    const cards = [...document.querySelectorAll('.dashboard-info-card')];

    if (searchInput && cards.length > 0) {
        searchInput.addEventListener('input', (event) => {
            const query = event.target.value.trim().toLowerCase();

            cards.forEach((card) => {
                const text = card.textContent ? card.textContent.toLowerCase() : '';
                card.hidden = Boolean(query) && !text.includes(query);
            });
        });
    }
});