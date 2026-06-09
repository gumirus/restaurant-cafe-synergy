<?php
// =============================================
// УПРАВЛЕНИЕ СЕССИЯМИ
// =============================================

session_start();

// Проверка авторизации
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Проверка прав администратора
function isAdmin(): bool {
    return isset($_SESSION['access_rights']) && $_SESSION['access_rights'] === 'ADMIN';
}

// Получить текущего пользователя
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'phone' => $_SESSION['user_phone'],
        'access_rights' => $_SESSION['access_rights'],
    ];
}

// Перенаправление
function redirect(string $url): void {
    header("Location: $url");
    exit;
}
