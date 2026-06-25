<?php
require_once __DIR__ . '/header.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>

    <section class="auth-section">
        <div class="container">
            <div class="auth-form" id="register-form">
                <h2>Регистрация</h2>

                <!-- Шаг 1: Данные -->
                <div id="step-data">
                    <input type="tel" id="reg-phone" placeholder="Номер телефона" required>
                    <input type="email" id="reg-email" placeholder="Электронная почта">
                    <input type="password" id="reg-pass" placeholder="Пароль" required>
                    <input type="password" id="reg-pass2" placeholder="Подтвердите пароль" required>

                    <div class="verify-method">
                        <label>Способ подтверждения:</label>
                        <select id="reg-method">
                            <option value="email">По email</option>
                            <option value="sms">По SMS (демо)</option>
                        </select>
                    </div>

                    <button class="btn" id="btn-send-code" style="width:100%;">Отправить код подтверждения</button>
                    <p style="margin-top:15px;text-align:center;">Уже есть аккаунт? <a href="login.php">Войти</a></p>
                </div>

                <!-- Шаг 2: Код -->
                <div id="step-code" style="display:none;">
                    <p style="margin-bottom:15px;color:var(--color-text-light);" id="code-sent-to">Код отправлен</p>
                    <div style="display:flex;gap:10px;justify-content:center;margin-bottom:20px;">
                        <input type="text" id="reg-code" placeholder="000000" maxlength="6"
                               style="width:180px;text-align:center;font-size:1.5rem;letter-spacing:8px;font-weight:700;">
                    </div>
                    <button class="btn" id="btn-verify" style="width:100%;">Подтвердить</button>
                    <p style="margin-top:10px;text-align:center;font-size:0.85rem;">
                        <a href="#" id="btn-resend" style="color:var(--color-primary);">Отправить снова</a>
                    </p>
                    <div id="demo-code-display" style="display:none;margin-top:15px;padding:12px;background:var(--color-bg-section);border-radius:10px;text-align:center;border:1px solid var(--color-primary);">
                        <small style="color:var(--color-text-light);">Демо-режим: ваш код</small>
                        <div id="demo-code-value" style="font-size:2rem;font-weight:700;color:var(--color-primary);letter-spacing:10px;"></div>
                    </div>
                </div>

                <div id="reg-error" style="display:none;margin-top:15px;padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:0.9rem;"></div>
            </div>
        </div>
    </section>

    <style>
    .verify-method {
        margin: 15px 0;
    }
    .verify-method label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.85rem;
        color: var(--color-text-light);
    }
    .verify-method select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        font-size: 0.95rem;
        background: var(--color-bg);
        cursor: pointer;
    }
    </style>

    <script>
    document.getElementById('btn-send-code').addEventListener('click', async function() {
        const phone = document.getElementById('reg-phone').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const pass = document.getElementById('reg-pass').value;
        const pass2 = document.getElementById('reg-pass2').value;
        const method = document.getElementById('reg-method').value;
        const value = method === 'email' ? email : phone;
        const errorDiv = document.getElementById('reg-error');

        errorDiv.style.display = 'none';

        if (!phone || phone.length < 10) { showError('Введите корректный номер телефона'); return; }
        if (!pass || pass.length < 4) { showError('Пароль должен быть не менее 4 символов'); return; }
        if (pass !== pass2) { showError('Пароли не совпадают'); return; }
        if (method === 'email' && !email) { showError('Укажите email для подтверждения по почте'); return; }
        if (method === 'sms' && (!phone || phone.length < 10)) { showError('Укажите телефон для SMS'); return; }

        this.textContent = '⏳ Отправка...';
        this.disabled = true;

        try {
            const resp = await fetch('../backend/send_code.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'method=' + method + '&value=' + encodeURIComponent(value)
            });
            const data = await resp.json();

            if (data.success) {
                document.getElementById('step-data').style.display = 'none';
                document.getElementById('step-code').style.display = 'block';

                const label = method === 'email' ? 'на почту ' + email : 'по SMS на ' + phone;
                document.getElementById('code-sent-to').textContent = '✅ Код отправлен ' + label;

                if (data.demo_code) {
                    document.getElementById('demo-code-display').style.display = 'block';
                    document.getElementById('demo-code-value').textContent = data.demo_code;
                }
            } else {
                showError(data.message || 'Ошибка отправки кода');
            }
        } catch (e) {
            showError('Ошибка соединения');
        }

        this.textContent = 'Отправить код подтверждения';
        this.disabled = false;
    });

    document.getElementById('btn-verify').addEventListener('click', async function() {
        const phone = document.getElementById('reg-phone').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const pass = document.getElementById('reg-pass').value;
        const pass2 = document.getElementById('reg-pass2').value;
        const method = document.getElementById('reg-method').value;
        const value = method === 'email' ? email : phone;
        const code = document.getElementById('reg-code').value.trim();
        const errorDiv = document.getElementById('reg-error');

        errorDiv.style.display = 'none';

        if (!code || code.length !== 6) { showError('Введите 6-значный код'); return; }

        this.textContent = '⏳ Проверка...';
        this.disabled = true;

        try {
            // Verify code
            const vResp = await fetch('../backend/verify_code.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'method=' + method + '&value=' + encodeURIComponent(value) + '&code=' + code
            });
            const vData = await vResp.json();

            if (!vData.success) {
                showError(vData.message || 'Неверный код');
                this.textContent = 'Подтвердить';
                this.disabled = false;
                return;
            }

            // Register
            const rResp = await fetch('../backend/register.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'phone=' + encodeURIComponent(phone) + '&email=' + encodeURIComponent(email) +
                      '&password=' + encodeURIComponent(pass) + '&password_confirm=' + encodeURIComponent(pass2) +
                      '&verify_method=' + method
            });
            const rData = await rResp.json();

            if (rData.success) {
                window.location.href = rData.redirect || '../frontend/index.php';
            } else if (rData.require_verification) {
                showError('Код не подтверждён. Попробуйте снова.');
            } else {
                const msg = rData.errors ? rData.errors.join('<br>') : (rData.message || 'Ошибка регистрации');
                showError(msg);
            }
        } catch (e) {
            showError('Ошибка соединения');
        }

        this.textContent = 'Подтвердить';
        this.disabled = false;
    });

    document.getElementById('btn-resend').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('step-code').style.display = 'none';
        document.getElementById('step-data').style.display = 'block';
        document.getElementById('demo-code-display').style.display = 'none';
        document.getElementById('reg-code').value = '';
    });

    function showError(msg) {
        const el = document.getElementById('reg-error');
        el.innerHTML = msg;
        el.style.display = 'block';
    }
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
