<?php
// =============================================
// ДОБАВЛЕНИЕ БЛЮДА В КОРЗИНУ
// =============================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод']);
    exit;
}

$dish_id = (int)($_POST['dish_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($dish_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID блюда']);
    exit;
}

// Проверяем, есть ли уже такое блюдо в корзине
$stmt = $pdo->prepare("SELECT id, count FROM shopping_cart WHERE user_id = ? AND dish_id = ?");
$stmt->execute([$user_id, $dish_id]);
$existing = $stmt->fetch();

if ($existing) {
    // Проверяем лимит в 20 единиц
    if ($existing['count'] >= 20) {
        echo json_encode(['success' => false, 'error' => 'Максимальное количество — 20 шт']);
        exit;
    }
    // Увеличиваем количество
    $stmt = $pdo->prepare("UPDATE shopping_cart SET count = count + 1 WHERE id = ?");
    $stmt->execute([$existing['id']]);
} else {
    // Добавляем новую запись
    $stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, dish_id, count) VALUES (?, ?, 1)");
    $stmt->execute([$user_id, $dish_id]);
}

// Считаем общее количество в корзине
$stmt = $pdo->prepare("SELECT SUM(count) as total FROM shopping_cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = (int)$stmt->fetchColumn();

echo json_encode(['success' => true, 'cart_count' => $cart_count]);
