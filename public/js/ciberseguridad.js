document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    lucide.createIcons();
  }

  const form = document.getElementById('sec-filtros-form');
  const tipoSelect = document.getElementById('sec-tipo-evento');
  const severidadSelect = document.getElementById('sec-severidad');
  const feedbackBox = document.getElementById('sec-copy-feedback');

  if (form && tipoSelect) {
    tipoSelect.addEventListener('change', () => form.submit());
  }

  if (form && severidadSelect) {
    severidadSelect.addEventListener('change', () => form.submit());
  }

  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-copy-hash]');
    if (!btn) return;

    const hash = btn.getAttribute('data-copy-hash');
    if (!hash) return;

    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(hash);
      } else {
        const temp = document.createElement('textarea');
        temp.value = hash;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
      }

      if (feedbackBox) {
        feedbackBox.textContent = `Hash SHA-256 copiado al portapapeles: ${hash}`;
        feedbackBox.classList.add('is-visible');
      }
    } catch (_err) {
      if (feedbackBox) {
        feedbackBox.textContent = `Hash: ${hash}`;
        feedbackBox.classList.add('is-visible');
      }
    }
  });
});