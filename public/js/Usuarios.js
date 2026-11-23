document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
  }

  const formFiltros = document.getElementById('form-filtros-usuarios');
  const selectRol = document.getElementById('select-rol');
  const selectEstado = document.getElementById('select-estado');

  if (formFiltros) {
    [selectRol, selectEstado].forEach(select => {
      if (!select) return;
      select.addEventListener('change', () => {
        formFiltros.submit();
      });
    });
  }
});