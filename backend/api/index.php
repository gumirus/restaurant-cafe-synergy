<?php
// =============================================
// API РОУТЕР — единая точка входа для /api/*
// =============================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../helpers.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Убираем префикс /api/
$route = preg_replace('#^/api/#', '', $path);
$route = rtrim($route, '/');

// Маппинг роутов: 'метод путь' => файл
$routes = [
    // Каталог
    'GET dishes'           => __DIR__ . '/../getPopularDishes.php',
    'GET menu'             => __DIR__ . '/../getSpecialDishes.php',

    // Корзина
    'POST cart/add'        => __DIR__ . '/../cart_add.php',
    'GET cart'             => __DIR__ . '/../cart.php',
    'POST cart/update'     => __DIR__ . '/../cart_update.php',
    'POST cart/remove'     => __DIR__ . '/../cart_remove.php',
    'GET cart/count'       => __DIR__ . '/../cart_count.php',
    'POST cart/sync'       => __DIR__ . '/cart_sync.php',

    // Оформление
    'POST checkout'        => __DIR__ . '/../checkout.php',
    'POST pay'             => __DIR__ . '/../pay.php',

    // Авторизация
    'POST auth/login'      => __DIR__ . '/../login.php',
    'POST auth/register'   => __DIR__ . '/../register.php',
    'POST auth/logout'     => __DIR__ . '/../logout.php',
    'POST auth/send-code'  => __DIR__ . '/../send_code.php',
    'POST auth/verify-code'=> __DIR__ . '/../verify_code.php',
    'POST auth/reset-password' => __DIR__ . '/../reset_password.php',

    // Бронирования
    'POST bookings'        => __DIR__ . '/../createBooking.php',
    'POST bookings/feedback' => __DIR__ . '/../submitBookingFeedback.php',
    'POST bookings/status' => __DIR__ . '/../updateBookingStatus.php',
    'DELETE bookings'      => __DIR__ . '/../clearBookings.php',
    'DELETE bookings/history' => __DIR__ . '/../clearBookingHistory.php',

    // Отзывы
    'POST feedback'        => __DIR__ . '/../submitFeedback.php',
    'DELETE feedback'      => __DIR__ . '/../deleteReview.php',

    // Профиль
    'DELETE profile'       => __DIR__ . '/../delete_account.php',

    // Админка
    'POST admin/employee'  => __DIR__ . '/../createEmployee.php',
    'POST admin/employee/profile' => __DIR__ . '/../updateEmployeeProfile.php',
    'POST admin/dishes'    => __DIR__ . '/../createProduct.php',
    'PUT admin/dishes'     => __DIR__ . '/../updateProduct.php',
    'DELETE admin/dishes'  => __DIR__ . '/../deleteProduct.php',
    'POST admin/promotions' => __DIR__ . '/../createPromotion.php',
    'DELETE admin/promotions' => __DIR__ . '/../deletePromotion.php',
    'POST admin/dishes/popular' => __DIR__ . '/../togglePopular.php',
    'POST admin/dishes/special' => __DIR__ . '/../toggleSpecial.php',
    'POST admin/orders/status' => __DIR__ . '/../updateOrderStatus.php',
    'DELETE admin/orders'  => __DIR__ . '/../clearOrders.php',
    'DELETE admin/orders/history' => __DIR__ . '/../clearOrderHistory.php',
    'POST admin/upload'    => __DIR__ . '/../upload.php',

    // Сотрудники
    'GET employee'         => __DIR__ . '/../employee/index.php',
];

$key = "$method $route";

if (isset($routes[$key])) {
    require $routes[$key];
} else {
    jsonError("Маршрут не найден: $method /api/$route", 404);
}
