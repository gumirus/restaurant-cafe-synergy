<?php
// =============================================
// УПРАВЛЕНИЕ СЕССИЯМИ
// =============================================

require_once __DIR__ . '/../helpers.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Проверка авторизации
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Проверка прав администратора
function isAdmin(): bool {
    return isset($_SESSION['access_rights']) && $_SESSION['access_rights'] === 'ADMIN';
}

// Проверка прав сотрудника
function isEmployee(): bool {
    return isset($_SESSION['access_rights']) && $_SESSION['access_rights'] === 'EMPLOYEE';
}

// Проверка доступа к админке (админ или сотрудник)
function isStaff(): bool {
    return isAdmin() || isEmployee();
}

// Получить текущего пользователя
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'phone' => $_SESSION['user_phone'],
        'access_rights' => $_SESSION['access_rights'],
        'position' => $_SESSION['user_position'] ?? '',
    ];
}

// Перенаправление
function redirect(string $url): void {
    header("Location: $url");
    exit;
}
