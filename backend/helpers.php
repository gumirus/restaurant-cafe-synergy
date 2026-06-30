<?php
// =============================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// =============================================

/**
 * Безопасный вывод: экранирование для HTML
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Генерация CSRF-токена
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF-токена
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Единый JSON ответ (успех)
 */
function jsonSuccess($data = [], $message = 'OK') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => time(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Единый JSON ответ (ошибка)
 */
function jsonError($message = 'Ошибка', $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'timestamp' => time(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Получить JSON из тела запроса (для POST/PUT с content-type: application/json)
 */
function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Валидация email
 */
function isValidEmail($email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Валидация телефона (Россия)
 */
function isValidPhone($phone): bool {
    return preg_match('/^(\+7|8)?[\s\-]?\(?[0-9]{3}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/', trim($phone)) === 1;
}

/**
 * Обрезать текст до нужной длины
 */
function truncate($text, $maxLength = 100, $ellipsis = '...') {
    if (mb_strlen($text) <= $maxLength) return $text;
    return mb_substr($text, 0, $maxLength) . $ellipsis;
}
