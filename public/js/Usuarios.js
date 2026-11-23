document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    lucide.createIcons();
  }

  const selectRol = document.getElementById('select-rol');
  const selectEstado = document.getElementById('select-estado');
  const form = selectRol ? selectRol.closest('form') : null;

  if (form && selectRol) {
    selectRol.addEventListener('change', () => form.submit());
  }

  if (form && selectEstado) {
    selectEstado.addEventListener('change', () => form.submit());
  }
});