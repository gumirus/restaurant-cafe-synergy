# 🏗 Архитектура проекта restaurant-cafe

## Стек
```
Frontend: HTML5 + CSS3 + JavaScript (ES6+)
Backend:  PHP 8.2 (PDO)
Database: MySQL 8 (InnoDB, utf8mb4)
Deploy:   Docker + Railway
```

## Структура
```
restaurant-cafe/
├── frontend/              # Клиентская часть
│   ├── css/
│   │   ├── base.css       # Сброс, переменные, типографика
│   │   ├── layout.css     # Шапка, подвал, навигация
│   │   ├── components.css # Кнопки, карточки, формы, модалки
│   │   ├── sections.css   # Все секции страниц (hero, menu, team...)
│   │   └── responsive.css # Адаптивность (@media)
│   ├── js/                # Скрипты (модульная структура)
│   └── images/            # Статика
├── backend/               # PHP API
│   ├── config/            # Подключение к БД, сессии
│   ├── admin/             # Панель администратора
│   ├── api/               # API-эндпоинты
│   ├── helpers.php        # Утилиты (e(), CSRF, rate limit)
│   └── *.php              # Эндпоинты (login, cart, checkout...)
├── database/              # SQL-схема и миграции
├── docs/                  # Документация
├── docker-compose.yml     # 3 контейнера: web + db + phpmyadmin
└── Dockerfile             # PHP 8.2 + PDO + MySQL
```

## Безопасность (внедрено)
- PDO prepared statements — защита от SQL injection
- CSRF-токены на всех формах
- Rate limiting (5 попыток/5 мин)
- session_regenerate_id после логина
- HttpOnly + SameSite cookies
- XSS-защита: функция e() для вывода

## Рефакторинг CSS (2026-06-28)
Был один style.css (33KB, ~1600 строк).
Разбит на 5 модулей по функциональности.
header.php подключает модули, старый style.css сохранён.
