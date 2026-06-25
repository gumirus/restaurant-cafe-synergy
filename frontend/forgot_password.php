<?php
require_once __DIR__ . '/header.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>

    <section class="auth-section">
        <div class="container">
            <div class="auth-form" id="forgot-form">
                <h2>Восстановление пароля</h2>
                <p style="text-align:center;color:var(--color-text-light);margin-bottom:20px;font-size:0.9rem;">
                    Введите email или номер телефона, мы отправим код для сброса пароля.
                </p>

                <!-- Шаг 1: Ввод email/телефона -->
                <div id="step1">
                    <input type="text" id="reset-contact" placeholder="Email или номер телефона" required>
                    <button class="btn" id="btn-send-reset" style="width:100%;">Отправить код</button>
                    <p style="margin-top:10px;text-align:center;"><a href="login.php">Вспомнили пароль? Войти</a></p>
                </div>

                <!-- Шаг 2: Ввод кода + новый пароль -->
                <div id="step2" style="display:none;">
                    <div id="demo-code-reset" style="display:none;margin-bottom:15px;padding:12px;background:var(--color-bg-section);border-radius:10px;text-align:center;border:1px solid var(--color-primary);">
                        <small style="color:var(--color-text-light);">Ваш код (демо):</small>
                        <div id="demo-code-value-reset" style="font-size:2rem;font-weight:700;color:var(--color-primary);letter-spacing:10px;"></div>
                    </div>
                    <input type="text" id="reset-code" placeholder="Код из 6 цифр" maxlength="6"
                           style="text-align:center;font-size:1.5rem;letter-spacing:8px;font-weight:700;">
                    <input type="password" id="reset-pass" placeholder="Новый пароль" style="margin-top:10px;">
                    <input type="password" id="reset-pass2" placeholder="Подтвердите пароль" style="margin-top:10px;">
                    <button class="btn" id="btn-reset-pass" style="width:100%;margin-top:5px;">Сбросить пароль</button>
                    <p style="margin-top:10px;text-align:center;"><a href="#" id="btn-back-step1">← Назад</a></p>
                </div>

                <div id="reset-error" style="display:none;margin-top:15px;padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:0.9rem;"></div>
            </div>
        </div>
    </section>

    <script>
    let resetMethod = 'email';
    let resetValue = '';

    document.getElementById('btn-send-reset').addEventListener('click', async function() {
        const contact = document.getElementById('reset-contact').value.trim();
        if (!contact) { showResetError('Введите email или телефон'); return; }

        resetMethod = contact.includes('@') ? 'email' : 'sms';
        resetValue = contact;

        this.textContent = '⏳...';
        this.disabled = true;

        try {
            const resp = await fetch('../backend/send_code.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'method=' + resetMethod + '&value=' + encodeURIComponent(contact)
            });
            const data = await resp.json();

            if (data.success) {
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                if (data.demo_code) {
                    document.getElementById('demo-code-reset').style.display = 'block';
                    document.getElementById('demo-code-value-reset').textContent = data.demo_code;
                }
            } else {
                showResetError(data.message || 'Ошибка отправки кода');
            }
        } catch (e) {
            showResetError('Ошибка соединения');
        }

        this.textContent = 'Отправить код';
        this.disabled = false;
    });

    document.getElementById('btn-reset-pass').addEventListener('click', async function() {
        const code = document.getElementById('reset-code').value.trim();
        const pass = document.getElementById('reset-pass').value;
        const pass2 = document.getElementById('reset-pass2').value;

        if (!code || code.length !== 6) { showResetError('Введите 6-значный код'); return; }
        if (!pass || pass.length < 4) { showResetError('Пароль должен быть не менее 4 символов'); return; }
        if (pass !== pass2) { showResetError('Пароли не совпадают'); return; }

        this.textContent = '⏳...';
        this.disabled = true;

        try {
            // Проверяем код
            const vResp = await fetch('../backend/verify_code.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'method=' + resetMethod + '&value=' + encodeURIComponent(resetValue) + '&code=' + code
            });
            const vData = await vResp.json();

            if (!vData.success) {
                showResetError(vData.message || 'Неверный код');
                this.textContent = 'Сбросить пароль';
                this.disabled = false;
                return;
            }

            // Сбрасываем пароль
            const rResp = await fetch('../backend/reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'login=' + encodeURIComponent(resetValue) + '&password=' + encodeURIComponent(pass)
            });
            const rData = await rResp.json();

            if (rData.success) {
                window.location.href = rData.redirect || 'login.php?reset=1';
            } else {
                showResetError(rData.message || 'Ошибка сброса пароля');
            }
        } catch (e) {
            showResetError('Ошибка соединения');
        }

        this.textContent = 'Сбросить пароль';
        this.disabled = false;
    });

    document.getElementById('btn-back-step1').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        document.getElementById('demo-code-reset').style.display = 'none';
    });

    function showResetError(msg) {
        const el = document.getElementById('reset-error');
        el.innerHTML = msg;
        el.style.display = 'block';
    }
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>