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
            <form class="auth-form" id="login-form" method="POST" action="../backend/login.php">
                <h2>Вход</h2>
                <input type="tel" name="phone" placeholder="Номер телефона" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" class="btn">Войти</button>
                <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </form>
        </div>
    </section>

<?php require_once __DIR__ . '/footer.php'; ?>
