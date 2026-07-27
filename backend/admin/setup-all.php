<?php
// =============================================
// ПОЛНАЯ НАСТРОЙКА ПОЛЬЗОВАТЕЛЕЙ И СОТРУДНИКОВ
// Запустить 1 раз в браузере, потом удалить файл
// =============================================

require_once __DIR__ . '/config/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔧 Полная настройка</h1>";

try {
    // ==============================
    // 1. Добавляем EMPLOYEE в access_rights, если нет
    // ==============================
    $stmt = $pdo->query("SELECT COUNT(*) FROM access_rights WHERE name = 'EMPLOYEE'");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO access_rights (name) VALUES ('EMPLOYEE')");
        echo "<p>✅ Добавлено право EMPLOYEE</p>";
    } else {
        echo "<p>✅ Право EMPLOYEE уже существует</p>";
    }

    // ==============================
    // 2. Добавляем колонку position в users, если нет
    // ==============================
    $hasPosition = false;
    try {
        $pdo->query("SELECT position FROM users LIMIT 1");
        $hasPosition = true;
    } catch (Exception $e) {
        $hasPosition = false;
    }
    if (!$hasPosition) {
        $pdo->exec("ALTER TABLE users ADD COLUMN position VARCHAR(100) DEFAULT NULL AFTER avatar");
        echo "<p>✅ Добавлена колонка position в users</p>";
    } else {
        echo "<p>✅ Колонка position уже существует</p>";
    }

    // ==============================
    // 3. Обновляем админа: имя = gumirus
    // ==============================
    $stmt = $pdo->prepare("UPDATE users SET name = 'gumirus', position = NULL WHERE phone = ?");
    $stmt->execute(['+79990000001']);
    echo "<p>✅ Админ: имя → gumirus</p>";

    // ==============================
    // 4. Настраиваем сотрудников
    // ==============================
    $employees = [
        ['phone' => '+79990000003', 'name' => 'Анна',      'position' => 'Су-шеф',        'password' => 'pass123'],
        ['phone' => '+79990000004', 'name' => 'Сергей',    'position' => 'Повар',         'password' => 'pass123'],
        ['phone' => '+79990000005', 'name' => 'Мария',     'position' => 'Официант',      'password' => 'pass123'],
        ['phone' => '+79990000006', 'name' => 'Дмитрий',   'position' => 'Ст. официант',  'password' => 'pass123'],
        ['phone' => '+79990000007', 'name' => 'Елена',     'position' => 'Кондитер',      'password' => 'pass123'],
        ['phone' => '+79990000008', 'name' => 'Алексей',   'position' => 'Бариста',       'password' => 'pass123'],
    ];

    // ID для EMPLOYEE в access_rights
    $empId = $pdo->query("SELECT id FROM access_rights WHERE name = 'EMPLOYEE'")->fetchColumn();

    $stmtUpdateUser = $pdo->prepare("
        UPDATE users 
        SET name = ?, position = ?, password = ?, access_rights_id = ?
        WHERE phone = ?
    ");

    $stmtPersonal = $pdo->prepare("
        INSERT INTO personal (position_id, full_name, phone) 
        VALUES (?, ?, ?)
    ");

    // Добавляем недостающие должности в positions
    $neededPositions = ['Су-шеф', 'Ст. официант', 'Кондитер', 'Бариста'];
    foreach ($neededPositions as $pos) {
        $exists = $pdo->query("SELECT COUNT(*) FROM positions WHERE name = " . $pdo->quote($pos))->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT INTO positions (name) VALUES (" . $pdo->quote($pos) . ")");
            echo "<p>➕ Добавлена должность: {$pos}</p>";
        }
    }

    // Map positions to position_ids
    $posList = $pdo->query("SELECT id, name FROM positions")->fetchAll(PDO::FETCH_KEY_PAIR);
    $positionMap = [];
    foreach ($posList as $id => $name) {
        $positionMap[$name] = $id;
    }

    foreach ($employees as $emp) {
        $hash = password_hash($emp['password'], PASSWORD_DEFAULT);

        // Обновляем пользователя
        $stmtUpdateUser->execute([
            $emp['name'],
            $emp['position'],
            $hash,
            $empId,
            $emp['phone'],
        ]);

        // Создаём запись в personal
        $posId = $positionMap[$emp['position']] ?? null;
        if ($posId) {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE phone = ?");
            $exists->execute([$emp['phone']]);
            if ($exists->fetchColumn() == 0) {
                $stmtPersonal->execute([$posId, $emp['name'], $emp['phone']]);
            }
        }

        echo "<p>✅ {$emp['name']} ({$emp['phone']}) — {$emp['position']}</p>";
    }

    // ==============================
    // 5. Итог
    // ==============================
    echo "<hr>";
    echo "<h2>📋 Итоговые данные для входа</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>";
    echo "<tr><th>Роль</th><th>Телефон</th><th>Пароль</th><th>Имя</th></tr>";
    echo "<tr><td>👑 Админ (Шеф-повар)</td><td>+79990000001</td><td>admin123</td><td>gumirus</td></tr>";
    echo "<tr><td>👤 Пользователь</td><td>+79990000002</td><td>user123</td><td>Руслан</td></tr>";
    echo "<tr><td>👨‍🍳 Су-шеф</td><td>+79990000003</td><td>pass123</td><td>Анна</td></tr>";
    echo "<tr><td>👨‍🍳 Повар</td><td>+79990000004</td><td>pass123</td><td>Сергей</td></tr>";
    echo "<tr><td>🧑‍💼 Официант</td><td>+79990000005</td><td>pass123</td><td>Мария</td></tr>";
    echo "<tr><td>🧑‍💼 Ст. официант</td><td>+79990000006</td><td>pass123</td><td>Дмитрий</td></tr>";
    echo "<tr><td>👩‍🍳 Кондитер</td><td>+79990000007</td><td>pass123</td><td>Елена</td></tr>";
    echo "<tr><td>☕ Бариста</td><td>+79990000008</td><td>pass123</td><td>Алексей</td></tr>";
    echo "</table>";

    echo "<p style='color:red;margin-top:20px;'>⚠️ Удалите файл <strong>backend/setup-all.php</strong> после использования!</p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
