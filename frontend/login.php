<?php
require_once __DIR__ . '/header.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>

    <section class="auth-section">
        <div class="container">
            <div class="auth-form" id="login-form">
                <h2>Вход</h2>
                <input type="text" id="login-phone" placeholder="Телефон или email" required>
                <input type="password" id="login-pass" placeholder="Пароль" required>
                <button class="btn" id="btn-login" style="width:100%;">Войти</button>
                <p style="margin-top:10px;"><a href="forgot_password.php">Забыли пароль?</a></p>
                <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </div>
        </div>
    </section>

    <!-- Модалка ошибки -->
    <div id="login-error-modal" class="modal-overlay" onclick="if(event.target===this)closeModal('login-error-modal')">
        <div class="modal-content modal-error">
            <button class="modal-close-btn" onclick="closeModal('login-error-modal')">×</button>
            <div class="modal-icon">😕</div>
            <h2>Ошибка входа</h2>
            <div id="login-error-list"></div>
            <button class="btn" onclick="closeModal('login-error-modal')" style="width:100%;">Понятно</button>
        </div>
    </div>

    <style>
    .modal-overlay {
        position: fixed; inset:0; z-index:99999;
        background: rgba(0,0,0,0.7); display:flex; align-items:center; justify-content:center;
        visibility:hidden; opacity:0; transition:all 0.3s;
    }
    .modal-overlay.active { visibility:visible; opacity:1; }
    .modal-content {
        background: var(--color-bg-section); border-radius:20px;
        max-width:420px; width:90%; padding:40px; text-align:center;
        box-shadow:0 25px 80px rgba(0,0,0,0.5);
        transform:scale(0.85) translateY(20px); transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
        position:relative;
    }
    .modal-overlay.active .modal-content { transform:scale(1) translateY(0); }
    .modal-close-btn {
        position:absolute; top:12px; right:16px;
        background:none; border:none; font-size:1.8rem; color:var(--color-text-light);
        cursor:pointer; line-height:1;
    }
    .modal-icon { font-size:3rem; margin-bottom:12px; }
    .modal-content h2 { font-family:var(--font-heading); color:var(--color-text); margin-bottom:12px; font-size:1.4rem; }
    .modal-content ul { list-style:none; padding:0; margin:0 0 15px; }
    .modal-content li { padding:8px; margin-bottom:6px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#dc2626; font-size:0.9rem; }
    .modal-error .modal-icon { color:#dc2626; }
    </style>

    <script>
    document.getElementById('btn-login').addEventListener('click', async function() {
        const login = document.getElementById('login-phone').value.trim();
        const pass = document.getElementById('login-pass').value;

        if (!login || !pass) {
            showLoginError(['Заполните все поля']);
            return;
        }

        this.textContent = '⏳...';
        this.disabled = true;

        try {
            const resp = await fetch('../backend/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'phone=' + encodeURIComponent(login) + '&password=' + encodeURIComponent(pass)
            });
            const data = await resp.json();

            if (data.success) {
                window.location.href = data.redirect || '../frontend/index.php';
            } else {
                showLoginError(data.errors || [data.message || 'Ошибка входа']);
            }
        } catch (e) {
            showLoginError(['Ошибка соединения']);
        }

        this.textContent = 'Войти';
        this.disabled = false;
    });

    function showLoginError(errors) {
        const list = document.getElementById('login-error-list');
        list.innerHTML = '<ul>' + errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        document.getElementById('login-error-modal').classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    // Enter key support
    document.getElementById('login-pass').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('btn-login').click();
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
