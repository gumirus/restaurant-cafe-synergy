<?php
// =============================================
// ОЧИСТКА ЗАКАЗОВ В АДМИН-ПАНЕЛИ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$action = $_GET['action'] ?? '';

if ($action === 'completed') {
    // Удаляем только выполненные заказы
    $pdo->exec("DELETE FROM orders WHERE status = 'completed'");
    header('Location: ../backend/admin/index.php?page=orders&success=Завершённые+заказы+очищены');
} elseif ($action === 'uncompleted') {
    // Удаляем все незавершённые заказы (кроме корзины)
    $pdo->exec("DELETE FROM orders WHERE status NOT IN ('completed', 'cart')");
    header('Location: ../backend/admin/index.php?page=orders&success=Незавершённые+заказы+очищены');
} elseif ($action === 'all') {
    // Удаляем все заказы (кроме корзины)
    $pdo->exec("DELETE FROM orders WHERE status != 'cart'");
    header('Location: ../backend/admin/index.php?page=orders&success=Все+заказы+очищены');
} else {
    header('Location: ../backend/admin/index.php?page=orders&error=Неизвестное+действие');
}
exit;
