<?php
require_once __DIR__ . '/config/db.php';

$photos = [
    1 => 'uploads/dishes/1-caesar.jpg',
    2 => 'uploads/dishes/2-greek.jpg',
    3 => 'uploads/dishes/3-tom-yam.jpg',
    4 => 'uploads/dishes/4-borscht.jpg',
    5 => 'uploads/dishes/5-steak.jpg',
    6 => 'uploads/dishes/6-carbonara.jpg',
    7 => 'uploads/dishes/7-tiramisu.jpg',
    8 => 'uploads/dishes/8-cheesecake.jpg',
    9 => 'uploads/dishes/9-lemonade.jpg',
    10 => 'uploads/dishes/10-coffee.jpg',
    11 => 'uploads/dishes/11-bowl.jpg',
    12 => 'uploads/dishes/12-pizza.jpg',
    13 => 'uploads/dishes/13-ramen.webp',
    14 => 'uploads/dishes/14-salad.jpg',
    15 => 'uploads/dishes/15-fish.jpg',
    16 => 'uploads/dishes/16-chicken.jpg',
    17 => 'uploads/dishes/17-sushi.jpg',
    18 => 'uploads/dishes/18-burger.jpg',
    19 => 'uploads/dishes/19-smoothie.jpg',
    20 => 'uploads/dishes/20-icecream.jpg',
    21 => 'uploads/dishes/21-kholodets.jpg',
    22 => 'uploads/dishes/22-okroshka.jpg',
];

$stmt = $pdo->prepare("UPDATE dishes SET image = ? WHERE id = ?");
foreach ($photos as $id => $img) {
    $stmt->execute([$img, $id]);
    echo "✅ $id\n";
}
echo "✅ Все фото исправлены!\n";
