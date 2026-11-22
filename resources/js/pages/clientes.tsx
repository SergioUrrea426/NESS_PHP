import '../../css/clientes.css';

const filterForm = document.querySelector<HTMLFormElement>('#clientes-filtros');

if (filterForm) {
    const filterSelects = filterForm.querySelectorAll<HTMLSelectElement>('select');
    const submitButton = filterForm.querySelector<HTMLButtonElement>(
        'button[type="submit"]',
    );

    filterSelects.forEach((select) => {
        select.addEventListener('change', () => filterForm.requestSubmit());
    });

    filterForm.addEventListener('submit', () => {
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Filtrando...';
        }
    });
}
