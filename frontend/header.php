<?php
// =============================================
// ОБЩАЯ ШАПКА САЙТА (с проверкой авторизации)
// =============================================
require_once __DIR__ . '/../backend/config/session.php';

$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bean Scene — Ресторан премиум-кухни</title>
    <link rel="stylesheet" href="css/color.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg+xml" href="images/logo.svg">
</head>
<body>

    <!-- ========== HEADER ========== -->
    <header class="header scrolled" id="header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="images/logo.svg" alt="Bean Scene" height="50">
            </a>
            <nav class="nav">
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
                    <a href="cart.php" class="cart-btn" title="Корзина">
                        🛒 <span id="cart-count" class="cart-count">0</span>
                    </a>
                    <span class="user-info"><?= htmlspecialchars($_SESSION['user_phone']) ?></span>
                    <a href="../backend/logout.php" class="btn-logout">Выйти</a>
                <?php else: ?>
                    <a href="login.php">Войти</a>
                    <a href="register.php" class="btn-primary">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
