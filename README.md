# 🍽 Веб-ресурс ресторана/кафе

## 📌 О проекте
Проект разрабатывается в рамках учебной и производственной практики ПМ.09 Университета «Синергия». 
Представляет собой полноценный веб-сайт ресторана с клиентской и серверной частью.

## 🛠 Технологии
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 8+ (PDO, сессии)
- **Database:** MySQL 8 (InnoDB, utf8mb4)
- **Инструменты:** Figma, draw.io, phpMyAdmin, GitHub

## 📁 Структура проекта
```
restaurant-cafe/
├── frontend/           # Клиентская часть
│   ├── index.html      # Главная страница
│   ├── about.html      # О ресторане
│   ├── menu.html       # Меню с фильтрацией
│   ├── promotions.html # Акции
│   ├── news.html       # Новости
│   ├── contact.html    # Контакты
│   ├── cart.html       # Корзина
│   ├── login.html      # Вход
│   ├── register.html   # Регистрация
│   ├── css/            # Стили
│   │   ├── color.css   # CSS-переменные (цвета)
│   │   └── style.css   # Основные стили
│   ├── js/             # Скрипты
│   │   ├── main.js     # Основные функции
│   │   ├── faq.js      # FAQ аккордеон
│   │   ├── filter.js   # Фильтр меню
│   │   ├── cart.js     # Корзина (localStorage)
│   │   └── validation.js # Валидация форм
│   └── images/         # Изображения
├── backend/            # Серверная часть
│   ├── config/
│   │   ├── db.php      # Подключение к БД (PDO)
│   │   └── session.php # Управление сессиями
│   ├── login.php       # Авторизация
│   ├── register.php    # Регистрация
│   ├── logout.php      # Выход
│   ├── cart.php        # API корзины
│   ├── order.php       # Оформление заказа
│   ├── upload.php      # Загрузка изображений
│   ├── createProduct.php # Добавление блюда
│   ├── admin/          # Админ-панель
│   └── uploads/        # Загруженные файлы
├── database/
│   └── restaurant.sql  # Дамп БД (11 таблиц)
├── design/             # Макеты Figma/draw.io
└── docs/               # Документация
```

## 🗄 База данных (11 таблиц)
1. `access_rights` — права доступа (ADMIN/USER)
2. `users` — пользователи
3. `categories` — категории блюд
4. `dishes` — блюда
5. `positions` — должности
6. `personal` — персонал
7. `shopping_cart` — корзина
8. `orders` — заказы
9. `order_items` — состав заказа
10. `news` — новости
11. `reviews` — отзывы

## 🚀 Установка и запуск

### 1. Клонировать проект
```bash
cd /Users/gumirus/Documents/synergy/Практика\ ПМ.09/restaurant-cafe
```

### 2. Настроить БД
```bash
mysql -u root -p < database/restaurant.sql
```

### 3. Настроить подключение
Отредактировать `backend/config/db.php`:
```php
$username = 'root';
$password = 'ваш_пароль';
```

### 4. Запустить сервер
```bash
php -S localhost:8000
```

### 5. Открыть в браузере
```
http://localhost:8000/frontend/index.html
```

## 📋 План разработки (по неделям)

### Неделя 1 (02.07 – 06.07): Анализ + IDEF0
- [ ] Анализ предметной области
- [ ] Диаграмма бизнес-процессов (IDEF0)

### Неделя 2 (07.07 – 13.07): Дизайн
- [ ] Moodboard
- [ ] Прототип в Figma

### Неделя 3 (14.07 – 20.07): Frontend
- [ ] HTML-вёрстка (9 страниц)
- [ ] CSS-стилизация
- [ ] JavaScript (фильтры, корзина, FAQ, валидация)

### Неделя 4 (21.07 – 28.07): Backend + БД + Тесты
- [ ] MySQL (11 таблиц)
- [ ] PHP (авторизация, регистрация, корзина, заказы)
- [ ] Тест-кейсы
- [ ] Деплой на GitHub
- [ ] Отчёт-презентация

## 🔗 Полезные ссылки
- **Figma** — figma.com
- **draw.io** — draw.io
- **phpMyAdmin** — localhost/phpmyadmin
- **GitHub** — github.com
- **LMS Синергия** — lms.synergy.ru
