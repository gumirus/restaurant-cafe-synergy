<?php
// =============================================
// ОЧИСТКА БРОНИРОВАНИЙ
// =============================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isAdmin()) {
    header('Location: ../frontend/login.php');
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'cancelled') {
    // Удалить только отменённые
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE status = 'cancelled'");
    $stmt->execute();
    $count = $stmt->rowCount();
    header('Location: admin/index.php?page=bookings&success=Удалено ' . $count . ' отменённых бронирований');
} elseif ($action === 'all') {
    // Удалить все бронирования
    $stmt = $pdo->query("DELETE FROM bookings");
    $count = $stmt->rowCount();
    header('Location: admin/index.php?page=bookings&success=Удалено ' . $count . ' бронирований');
} else {
    header('Location: admin/index.php?page=bookings');
}
exit;
