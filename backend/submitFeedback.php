<?php
// =============================================
// ОБРАТНАЯ СВЯЗЬ ПО ЗАКАЗУ (лайк/дизлайк + комментарий)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn()) {
    header('Location: ../frontend/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $rating = $_POST['rating'] ?? ''; // 'like' или 'dislike'
    $comment = trim($_POST['comment'] ?? '');

    // Проверяем, что заказ принадлежит пользователю
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: ../frontend/profile.php?error=Заказ не найден');
        exit;
    }

    // Проверяем, что заказ выполнен (completed)
    if ($order['status'] !== 'completed') {
        header('Location: ../frontend/profile.php?error=Можно оценить только выполненный заказ');
        exit;
    }

    // Проверяем, что ещё не оставляли отзыв
    $stmt = $pdo->prepare("SELECT id FROM order_feedback WHERE order_id = ?");
    $stmt->execute([$order_id]);
    if ($stmt->fetch()) {
        header('Location: ../frontend/profile.php?error=Вы уже оставили отзыв на этот заказ');
        exit;
    }

    // Валидация рейтинга
    if (!in_array($rating, ['like', 'dislike'])) {
        header('Location: ../frontend/profile.php?error=Некорректная оценка');
        exit;
    }

    // Сохраняем
    $stmt = $pdo->prepare("INSERT INTO order_feedback (order_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $user_id, $rating, $comment ?: null]);

    header('Location: ../frontend/profile.php?success=Спасибо за ваш отзыв!');
    exit;
}

header('Location: ../frontend/profile.php');
exit;
