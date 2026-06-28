<?php
// =============================================
// ЗАЩИТА ОТ БРУТФОРСА (Rate Limiting)
// =============================================

/**
 * Проверяет лимит попыток для заданного ключа
 * @param string $key Уникальный ключ (например, 'login_192.168.1.1')
 * @param int $limit Максимум попыток
 * @param int $window Окно в секундах (по умолчанию 5 мин = 300с)
 * @return bool true если лимит не превышен, false если превышен
 */
function checkRateLimit($key, $limit = 5, $window = 300) {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    $storage = &$_SESSION['rate_limits'][$key];
    if (!is_array($storage)) {
        $storage = ['count' => 0, 'first_attempt' => time()];
    }
    if (time() - $storage['first_attempt'] > $window) {
        $storage = ['count' => 0, 'first_attempt' => time()];
    }
    if ($storage['count'] >= $limit) {
        return false;
    }
    $storage['count']++;
    return true;
}
