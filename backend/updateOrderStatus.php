<?php
// =============================================
// ОБНОВЛЕНИЕ СТАТУСА ЗАКАЗА (АДМИН)
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

$id = (int)($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

$allowedStatuses = ['new', 'confirmed', 'cancelled', 'completed'];

if ($id && in_array($status, $allowedStatuses)) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

redirect('admin/index.php?page=orders');
