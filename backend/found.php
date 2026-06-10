<?php
// =============================================
// ПОИСК БЛЮД
// =============================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    echo json_encode(['success' => true, 'dishes' => []]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.id, d.name, d.description, d.price, d.weight, d.image, c.name as category_name
    FROM dishes d
    JOIN categories c ON d.category_id = c.id
    WHERE d.name LIKE ? OR d.description LIKE ?
    LIMIT 20
");
$searchTerm = '%' . $query . '%';
$stmt->execute([$searchTerm, $searchTerm]);
$dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'dishes' => $dishes]);
