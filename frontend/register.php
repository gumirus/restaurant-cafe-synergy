<?php
require_once __DIR__ . '/header.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>

    <section class="auth-section">
        <div class="container">
            <form class="auth-form" id="register-form" method="POST" action="../backend/register.php">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <h2>Регистрация</h2>
                <input type="tel" id="reg-phone" name="phone" placeholder="Номер телефона" required>
                <input type="email" id="reg-email" name="email" placeholder="Электронная почта">
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="password" name="password_confirm" placeholder="Подтвердите пароль" required>

                <div id="verify-section" style="margin:15px 0;padding:15px;background:var(--color-bg-section);border-radius:10px;border:1px solid var(--color-border);">
                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
                        <input type="text" id="verify-code" name="verify_code" placeholder="Код из 6 цифр" maxlength="6"
                               style="flex:1;text-align:center;font-size:1.2rem;letter-spacing:6px;font-weight:700;padding:10px;">
                        <button type="button" id="btn-send-code" class="btn" style="white-space:nowrap;padding:10px 16px;font-size:12px;">📨 Код</button>
                    </div>
                    <div id="demo-code-box" style="display:none;margin-top:8px;padding:8px 12px;background:var(--color-bg);border-radius:8px;text-align:center;border:1px solid var(--color-primary);">
                        <small style="color:var(--color-text-light);">Ваш код (демо):</small>
                        <span id="demo-code-value" style="font-size:1.5rem;font-weight:700;color:var(--color-primary);letter-spacing:6px;margin-left:8px;"></span>
                    </div>
                </div>

                <button type="submit" class="btn">Зарегистрироваться</button>
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </form>
        </div>
    </section>

    <script>
    document.getElementById('btn-send-code').addEventListener('click', async function() {
        const phone = document.getElementById('reg-phone').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const method = email ? 'email' : 'sms';
        const value = method === 'email' ? email : phone;

        if (method === 'email' && !email) { alert('Укажите email'); return; }
        if (method === 'sms' && (!phone || phone.length < 10)) { alert('Укажите номер телефона'); return; }

        this.textContent = '⏳...';
        this.disabled = true;

        try {
            const resp = await fetch('../backend/send_code.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'method=' + method + '&value=' + encodeURIComponent(value)
            });
            const data = await resp.json();

            if (data.success && data.demo_code) {
                document.getElementById('demo-code-box').style.display = 'block';
                document.getElementById('demo-code-value').textContent = data.demo_code;
            }
        } catch (e) {
            alert('Ошибка');
        }

        this.textContent = '📨 Код';
        this.disabled = false;
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
