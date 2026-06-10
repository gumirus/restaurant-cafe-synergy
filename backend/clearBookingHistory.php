<?php
// =============================================
// ОЧИСТКА ИСТОРИИ БРОНИРОВАНИЙ ПОЛЬЗОВАТЕЛЯ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn()) {
    die('Доступ запрещён');
}

$user_id = $_SESSION['user_id'];

// Получаем телефон пользователя для поиска бронирований без user_id
$stmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Удаляем бронирования пользователя (по user_id или по телефону)
$stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ? OR (user_id IS NULL AND phone = ?)");
$stmt->execute([$user_id, $user['phone']]);

header('Location: ../frontend/profile.php?success=История+бронирований+очищена');
exit;
