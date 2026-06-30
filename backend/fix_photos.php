<?php
require_once __DIR__ . '/config/db.php';

// Маппинг: название блюда → правильное фото
$photos = [
    'Цезарь с курицей' => 'uploads/dishes/1-caesar.jpg',
    'Греческий салат' => 'uploads/dishes/2-greek.jpg',
    'Том Ям' => 'uploads/dishes/3-tom-yam.jpg',
    'Борщ' => 'uploads/dishes/4-borscht.jpg',
    'Стейк Рибай' => 'uploads/dishes/5-steak.jpg',
    'Паста Карбонара' => 'uploads/dishes/6-carbonara.jpg',
    'Тирамису' => 'uploads/dishes/7-tiramisu.jpg',
    'Чизкейк' => 'uploads/dishes/8-cheesecake.jpg',
    'Лимонад' => 'uploads/dishes/9-lemonade.jpg',
    'Кофе' => 'uploads/dishes/10-coffee.jpg',
    'Боул с киноа' => 'uploads/dishes/11-bowl.jpg',
    'Пицца Маргарита' => 'uploads/dishes/12-pizza.jpg',
    'Рамен' => 'uploads/dishes/13-ramen.webp',
    'Нисуаз' => 'uploads/dishes/14-salad.jpg',
    'Лосось с овощами' => 'uploads/dishes/15-fish.jpg',
    'Куриный рулет' => 'uploads/dishes/16-chicken.jpg',
    'Суши-сет' => 'uploads/dishes/17-sushi.jpg',
    'Бургер' => 'uploads/dishes/18-burger.jpg',
    'Смузи' => 'uploads/dishes/19-smoothie.jpg',
    'Мороженое' => 'uploads/dishes/20-icecream.jpg',
    'Холодец' => 'uploads/dishes/21-kholodets.jpg',
    'Окрошка' => 'uploads/dishes/22-okroshka.jpg',
];

$stmt = $pdo->prepare("UPDATE dishes SET image = ? WHERE name = ?");
$count = 0;
foreach ($photos as $name => $img) {
    $stmt->execute([$img, $name]);
    if ($stmt->rowCount()) {
        echo "✅ $name → $img\n";
        $count++;
    } else {
        echo "❌ $name — не найдено!\n";
    }
}
echo "\n✅ Исправлено $count блюд!\n";
