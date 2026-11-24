document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');
    const submitBtn = document.getElementById('submitBtn');

    if (togglePasswordBtn && passwordInput && eyeIcon) {
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon.classList.toggle('fa-eye', !isPassword);
            eyeIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let isValid = true;
            const usernameValue = usernameInput ? usernameInput.value.trim() : '';
            const passwordValue = passwordInput ? passwordInput.value.trim() : '';

            if (usernameInput && usernameError) {
                if (usernameValue === '') {
                    usernameInput.classList.add('input-error');
                    usernameError.classList.remove('hidden');
                    isValid = false;
                } else {
                    usernameInput.classList.remove('input-error');
                    usernameError.classList.add('hidden');
                }
            }

            if (passwordInput && passwordError) {
                if (passwordValue === '') {
                    passwordInput.classList.add('input-error');
                    passwordError.classList.remove('hidden');
                    isValid = false;
                } else {
                    passwordInput.classList.remove('input-error');
                    passwordError.classList.add('hidden');
                }
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
                submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> Conectando...';
            }
        });
    }
});