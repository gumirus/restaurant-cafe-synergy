<?php
// =============================================
// ОБЩАЯ ШАПКА САЙТА (с проверкой авторизации)
// =============================================
require_once __DIR__ . '/../backend/config/session.php';
require_once __DIR__ . '/../backend/config/db.php';

$current_page = basename($_SERVER['SCRIPT_NAME']);

// Получаем аватар пользователя, если авторизован
$userAvatar = null;
$userAvatarData = null;
$userName = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT avatar, avatar_data, name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
    $userAvatar = $userData['avatar'] ?? null;
    $userAvatarData = $userData['avatar_data'] ?? null;
    $userName = $userData['name'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Точка Кипения — Ресторан премиум-кухни</title>
    <link rel="stylesheet" href="css/base.css?v=1782653036">
    <link rel="stylesheet" href="css/layout.css?v=1782653036">
    <link rel="stylesheet" href="css/components.css?v=1782653036">
    <link rel="stylesheet" href="css/sections.css?v=1782653036">
    <link rel="stylesheet" href="css/responsive.css?v=1782653036">
    <link rel="icon" type="image/svg+xml" href="images/logo.svg">
    <script>
    function openAboutModal() {
        var m = document.getElementById('aboutModal');
        if (m) { m.classList.add('active'); document.body.style.overflow = 'hidden'; }
    }
    function closeAboutModal() {
        var m = document.getElementById('aboutModal');
        if (m) { m.classList.remove('active'); document.body.style.overflow = ''; }
    }
    </script>
</head>
<body>

    <!-- ========== HEADER ========== -->
    <header class="header scrolled" id="header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="images/logo.svg" alt="Точка Кипения" height="50">
            </a>
            <button class="burger-btn" id="burgerBtn" onclick="toggleMobileMenu()" aria-label="Меню">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </button>
            <div class="mobile-menu-dropdown" id="mobileMenuDropdown">
                <ul>
                    <li><a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>" onclick="toggleMobileMenu()">Главная</a></li>
                    <li><a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>" onclick="toggleMobileMenu()">О нас</a></li>
                    <li><a href="menu.php" class="<?= $current_page === 'menu.php' ? 'active' : '' ?>" onclick="toggleMobileMenu()">Меню</a></li>
                    <li><a href="promotions.php" class="<?= $current_page === 'promotions.php' ? 'active' : '' ?>" onclick="toggleMobileMenu()">Акции</a></li>
                    <li><a href="news.php" class="<?= $current_page === 'news.php' ? 'active' : '' ?>" onclick="toggleMobileMenu()">Новости</a></li>
                    <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>" onclick="toggleMobileMenu()">Контакты</a></li>
                </ul>
            </div>
            <div class="nav-overlay" id="navOverlay" onclick="toggleMobileMenu()"></div>
            <nav class="nav">
                <div class="nav-logo">
                    <img src="images/logo.svg" alt="Точка Кипения" height="40">
                </div>
                <ul>
                    <li><a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Главная</a></li>
                    <li><a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">О нас</a></li>
                    <li><a href="menu.php" class="<?= $current_page === 'menu.php' ? 'active' : '' ?>">Меню</a></li>
                    <li><a href="promotions.php" class="<?= $current_page === 'promotions.php' ? 'active' : '' ?>">Акции</a></li>
                    <li><a href="news.php" class="<?= $current_page === 'news.php' ? 'active' : '' ?>">Новости</a></li>
                    <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Контакты</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" title="Личный кабинет" class="header-avatar-link">
                        <?php if ($userAvatarData): ?>
                            <img src="<?= htmlspecialchars($userAvatarData) ?>" alt="Аватар" class="header-avatar">
                        <?php elseif ($userAvatar): ?>
                            <img src="uploads/<?= htmlspecialchars($userAvatar) ?>" alt="Аватар" class="header-avatar">
                        <?php else: ?>
                            <span class="header-avatar-placeholder">👤</span>
                        <?php endif; ?>
                    </a>
                    <a href="cart.php" class="cart-btn" title="Корзина">
                        🛒 <span id="cart-count" class="cart-count">0</span>
                    </a>
                    <span class="user-info"><?= htmlspecialchars($userName ?: $_SESSION['user_phone']) ?></span>
                    <a href="../backend/logout.php" class="btn-logout">Выйти</a>
                <?php else: ?>
                    <a href="login.php">Войти</a>
                    <a href="register.php" class="btn-primary">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
