<?php
// =============================================
// ОБНОВЛЕНИЕ СТАТУСА ЗАКАЗА
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isStaff()) {
    die('Доступ запрещён');
}

// Поддержка GET (админка) и POST (панель сотрудника)
$id = (int)($_POST['order_id'] ?? $_GET['id'] ?? 0);
$status = $_POST['status'] ?? $_GET['status'] ?? '';

$allowedStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];

if ($id && in_array($status, $allowedStatuses)) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

// Редирект обратно
$referer = $_SERVER['HTTP_REFERER'] ?? 'admin/index.php?page=orders';
redirect($referer);
