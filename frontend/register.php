<?php
require_once __DIR__ . '/header.php';
// Если уже авторизован — редирект
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>

    <section class="auth-section">
        <div class="container">
            <form class="auth-form" id="register-form" method="POST" action="../backend/register.php">
                <h2>Регистрация</h2>
                <input type="tel" name="phone" placeholder="Номер телефона" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="password" name="password_confirm" placeholder="Подтвердите пароль" required>
                <button type="submit" class="btn">Зарегистрироваться</button>
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </form>
        </div>
    </section>

<?php require_once __DIR__ . '/footer.php'; ?>
