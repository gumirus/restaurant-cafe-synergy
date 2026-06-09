// ========== VALIDATION (Валидация форм) ==========
document.addEventListener('DOMContentLoaded', function() {
    // Валидация формы входа
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const phone = this.querySelector('[name="phone"]').value;
            const password = this.querySelector('[name="password"]').value;

            if (!phone || phone.length < 10) {
                e.preventDefault();
                alert('Введите корректный номер телефона');
                return;
            }
            if (!password || password.length < 4) {
                e.preventDefault();
                alert('Пароль должен быть не менее 4 символов');
                return;
            }
        });
    }

    // Валидация формы регистрации
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const phone = this.querySelector('[name="phone"]').value;
            const password = this.querySelector('[name="password"]').value;
            const confirm = this.querySelector('[name="password_confirm"]').value;

            if (!phone || phone.length < 10) {
                e.preventDefault();
                alert('Введите корректный номер телефона');
                return;
            }
            if (!password || password.length < 4) {
                e.preventDefault();
                alert('Пароль должен быть не менее 4 символов');
                return;
            }
            if (password !== confirm) {
                e.preventDefault();
                alert('Пароли не совпадают');
                return;
            }
        });
    }

    // Валидация формы обратной связи
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = this.querySelector('[name="name"]');
            const phone = this.querySelector('[name="phone"]');
            const message = this.querySelector('[name="message"]');

            if (name && !name.value.trim()) {
                e.preventDefault();
                alert('Введите имя');
                return;
            }
            if (phone && (!phone.value || phone.value.length < 10)) {
                e.preventDefault();
                alert('Введите корректный номер телефона');
                return;
            }
            if (message && !message.value.trim()) {
                e.preventDefault();
                alert('Введите сообщение');
                return;
            }
        });
    }
});
