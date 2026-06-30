<?php
// =============================================
// ЗАЩИТА ОТ БРУТФОРСА (Rate Limiting)
// Файловое хранение — не зависит от сессии
// =============================================

define('RATE_LIMIT_DIR', __DIR__ . '/../storage/rate_limits');

/**
 * Проверяет лимит попыток для заданного ключа
 * @param string $key Уникальный ключ (например, 'login_192.168.1.1')
 * @param int $limit Максимум попыток
 * @param int $window Окно в секундах (по умолчанию 5 мин = 300с)
 * @return bool true если лимит не превышен, false если превышен
 */
function checkRateLimit($key, $limit = 5, $window = 300) {
    $dir = RATE_LIMIT_DIR;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    $file = "$dir/$safeKey.json";

    $data = ['count' => 0, 'first_attempt' => time()];
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
    }

    // Сброс если окно истекло
    if (time() - $data['first_attempt'] > $window) {
        $data = ['count' => 1, 'first_attempt' => time()];
        @file_put_contents($file, json_encode($data));
        return true;
    }

    // Превышен лимит
    if ($data['count'] >= $limit) {
        return false;
    }

    // Увеличиваем счётчик
    $data['count']++;
    @file_put_contents($file, json_encode($data));
    return true;
}

/**
 * Очистить rate limit для ключа (после успешного входа)
 */
function clearRateLimit($key) {
    $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    $file = RATE_LIMIT_DIR . "/$safeKey.json";
    if (file_exists($file)) {
        @unlink($file);
    }
}
