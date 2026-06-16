<?php
// =============================================
// РОУТЕР для PHP built-in сервера
// Позволяет:
//   / → frontend/index.php
//   /css/... → frontend/css/...
//   /js/... → frontend/js/...
//   /uploads/... → frontend/uploads/...
//   /backend/... → backend/...
// =============================================

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$rootDir = __DIR__;

// 1. Если файл существует — отдаём как есть
$filePath = $rootDir . $path;
if (file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// 2. Статика из frontend (css, js, images, uploads) — явно читаем и отдаём
$staticDirs = ['/css/', '/js/', '/images/', '/uploads/'];
$mimeTypes = ['css' => 'text/css', 'js' => 'application/javascript', 'svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'ico' => 'image/x-icon'];
foreach ($staticDirs as $dir) {
    if (strpos($path, $dir) === 0) {
        $frontendFile = $rootDir . '/frontend' . $path;
        if (file_exists($frontendFile) && !is_dir($frontendFile)) {
            $ext = pathinfo($frontendFile, PATHINFO_EXTENSION);
            if (isset($mimeTypes[$ext])) {
                header('Content-Type: ' . $mimeTypes[$ext]);
            }
            readfile($frontendFile);
            return true;
        }
    }
}

// 3. Корень сайта → frontend/index.php
if ($path === '/' || $path === '') {
    require $rootDir . '/frontend/index.php';
    return true;
}

// 4. PHP-файлы из frontend (about.php, menu.php, cart.php и т.д.)
$frontendPhp = $rootDir . '/frontend' . $path;
if (file_exists($frontendPhp) && !is_dir($frontendPhp)) {
    require $frontendPhp;
    return true;
}

// 5. Админка и employee — из backend/
$backendDirs = ['/admin/', '/employee/', '/config/'];
foreach ($backendDirs as $dir) {
    if (strpos($path, $dir) === 0) {
        $backendFile = $rootDir . '/backend' . $path;
        if (file_exists($backendFile) && !is_dir($backendFile)) {
            require $backendFile;
            return true;
        }
        // Ищем index.php в директории
        $indexPath = rtrim($backendFile, '/') . '/index.php';
        if (file_exists($indexPath)) {
            require $indexPath;
            return true;
        }
    }
}

// 6. Директории → ищем index.php
$indexPath = rtrim($filePath, '/') . '/index.php';
if (file_exists($indexPath)) {
    require $indexPath;
    return true;
}

// 6. Всё остальное — пусть PHP сам обработает (вернёт 404 если нет)
return false;
