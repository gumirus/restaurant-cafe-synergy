<?php
// =============================================
// НАСТРОЙКА АДМИНИСТРАТОРА
// =============================================
// Запусти один раз: php setup-admin.php
// Или открой в браузере: http://localhost/restaurant-cafe/backend/setup-admin.php
// =============================================

require_once __DIR__ . '/config/db.php';

echo "<h1>🔧 Настройка администратора</h1>";

try {
    // Создаём админа
    $phone = '+79990000001';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Проверяем, есть ли уже
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Обновляем права до админа
        $stmt = $pdo->prepare("UPDATE users SET access_rights_id = 1 WHERE phone = ?");
        $stmt->execute([$phone]);
        echo "<p style='color:green'>✅ Пользователь <strong>{$phone}</strong> обновлён до администратора</p>";
    } else {
        // Создаём нового
        $stmt = $pdo->prepare("INSERT INTO users (phone, password, access_rights_id) VALUES (?, ?, 1)");
        $stmt->execute([$phone, $hash]);
        echo "<p style='color:green'>✅ Администратор создан: <strong>{$phone}</strong></p>";
    }

    echo "<h3>📋 Данные для входа:</h3>";
    echo "<ul>";
    echo "<li><strong>Телефон:</strong> {$phone}</li>";
    echo "<li><strong>Пароль:</strong> {$password}</li>";
    echo "<li><strong>Админ-панель:</strong> <a href='admin/index.php'>admin/index.php</a></li>";
    echo "</ul>";

    echo "<hr>";
    echo "<h3>📋 Тестовый пользователь:</h3>";
    echo "<ul>";
    echo "<li><strong>Телефон:</strong> +79990000002</li>";
    echo "<li><strong>Пароль:</strong> user123</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
