<?php
require_once __DIR__ . '/config/db.php';

// Get current dishes ordered by ID, assign correct photos by position
$dishes = $pdo->query("SELECT id, name FROM dishes ORDER BY id")->fetchAll();
$photos = [
    '1-caesar.jpg', '2-greek.jpg', '3-tom-yam.jpg', '4-borscht.jpg',
    '5-steak.jpg', '6-carbonara.jpg', '7-tiramisu.jpg', '8-cheesecake.jpg',
    '9-lemonade.jpg', '10-coffee.jpg', '11-bowl.jpg', '12-pizza.jpg',
    '13-ramen.webp', '14-salad.jpg', '15-fish.jpg', '16-chicken.jpg',
    '17-sushi.jpg', '18-burger.jpg', '19-smoothie.jpg', '20-icecream.jpg',
    '21-kholodets.jpg', '22-okroshka.jpg',
];

$stmt = $pdo->prepare("UPDATE dishes SET image = ? WHERE id = ?");
foreach ($dishes as $i => $d) {
    $img = 'uploads/dishes/' . ($photos[$i] ?? 'placeholder.jpg');
    $stmt->execute([$img, $d['id']]);
    echo "✅ {$d['id']}: {$d['name']} → $img\n";
}
echo "\n✅ Все фото исправлены!\n";
