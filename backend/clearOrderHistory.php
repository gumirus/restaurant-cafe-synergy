<?php
// =============================================
// ОЧИСТКА ИСТОРИИ ЗАКАЗОВ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn()) {
    die('Доступ запрещён');
}

$user_id = $_SESSION['user_id'];

// Удаляем все заказы пользователя, кроме корзины
$stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ? AND status != 'cart'");
$stmt->execute([$user_id]);

header('Location: ../frontend/profile.php?success=История+заказов+очищена');
exit;
