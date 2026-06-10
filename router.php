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

// 2. Статика из frontend (css, js, images, uploads)
$staticDirs = ['/css/', '/js/', '/images/', '/uploads/'];
foreach ($staticDirs as $dir) {
    if (strpos($path, $dir) === 0) {
        $frontendFile = $rootDir . '/frontend' . $path;
        if (file_exists($frontendFile) && !is_dir($frontendFile)) {
            return false;
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

// 5. Директории → ищем index.php
$indexPath = rtrim($filePath, '/') . '/index.php';
if (file_exists($indexPath)) {
    require $indexPath;
    return true;
}

// 6. Всё остальное — пусть PHP сам обработает (вернёт 404 если нет)
return false;
