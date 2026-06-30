<?php
// =============================================
// РОУТЕР для PHP built-in сервера
// Позволяет:
//   / → frontend/index.php
//   /css/... → frontend/css/...
//   /js/... → frontend/js/...
//   /uploads/... → frontend/uploads/...
//   /backend/... → backend/...
//   /api/... → backend/api/... (с CORS)
// =============================================

// CORS для API-запросов
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Preflight (OPTIONS) — сразу 200
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
$mimeTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'svg' => 'image/svg+xml',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'ico' => 'image/x-icon',
    'woff2' => 'font/woff2',
    'woff' => 'font/woff',
    'ttf' => 'font/ttf',
];
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

// 4. API-роуты (/api/...) → backend/api/index.php
if (strpos($path, '/api/') === 0) {
    $apiFile = $rootDir . '/backend/api/index.php';
    if (file_exists($apiFile)) {
        require $apiFile;
        return true;
    }
}

// 5. PHP-файлы из frontend
$frontendPhp = $rootDir . '/frontend' . $path;
if (file_exists($frontendPhp) && !is_dir($frontendPhp)) {
    require $frontendPhp;
    return true;
}

// 6. Админка и employee — из backend/
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

// 7. Директории → ищем index.php
$indexPath = rtrim($filePath, '/') . '/index.php';
if (file_exists($indexPath)) {
    require $indexPath;
    return true;
}

// 8. 404
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'message' => 'Страница не найдена',
    'path' => $path,
], JSON_UNESCAPED_UNICODE);
