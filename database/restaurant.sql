-- =============================================
-- БАЗА ДАННЫХ: restaurant_db
-- Проект: Веб-ресурс ресторана/кафе
-- Практика ПМ.09 (Учебная + Производственная)
-- =============================================

CREATE DATABASE IF NOT EXISTS restaurant_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE restaurant_db;

-- ========== 1. ПРАВА ДОСТУПА ==========
CREATE TABLE access_rights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE  -- ADMIN / USER / EMPLOYEE
) ENGINE=InnoDB;

INSERT INTO access_rights (name) VALUES ('ADMIN'), ('USER'), ('EMPLOYEE');

-- ========== 2. ПОЛЬЗОВАТЕЛИ ==========
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) DEFAULT NULL,
    name VARCHAR(100) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    position VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    access_rights_id INT NOT NULL DEFAULT 2,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_rights_id) REFERENCES access_rights(id)
) ENGINE=InnoDB;

-- Миграция для существующей БД (добавление новых колонок, если их нет)
-- Запускается без ошибок, если колонки уже существуют
ALTER TABLE users ADD COLUMN IF NOT EXISTS name VARCHAR(100) DEFAULT NULL AFTER phone;
ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL AFTER name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL AFTER bio;
ALTER TABLE users ADD COLUMN IF NOT EXISTS position VARCHAR(100) DEFAULT NULL AFTER avatar;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
-- Orders columns now defined in CREATE TABLE
INSERT IGNORE INTO access_rights (name) VALUES ('EMPLOYEE');
ALTER TABLE bookings ADD COLUMN user_id INT DEFAULT NULL AFTER id;
-- Добавление колонок is_popular и is_special (для старых версий MySQL без IF NOT EXISTS)
ALTER TABLE dishes ADD COLUMN is_popular TINYINT(1) DEFAULT 0 AFTER image;
ALTER TABLE dishes ADD COLUMN is_special TINYINT(1) DEFAULT 0 AFTER is_popular;
ALTER TABLE dishes ADD COLUMN IF NOT EXISTS ingredients TEXT DEFAULT NULL AFTER description;

-- ========== 3. КАТЕГОРИИ БЛЮД ==========
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
    ('Салаты'),
    ('Супы'),
    ('Горячие блюда'),
    ('Десерты'),
    ('Напитки'),
    ('Холодные блюда');

-- ========== 4. БЛЮДА ==========
CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    ingredients TEXT DEFAULT NULL,  -- состав блюда
    price DECIMAL(10, 2) NOT NULL,
    weight INT DEFAULT 0,          -- вес в граммах
    image VARCHAR(255) DEFAULT NULL,
    is_popular TINYINT(1) DEFAULT 0,
    is_special TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

INSERT INTO dishes (category_id, name, description, price, weight, image) VALUES
    (1, 'Цезарь с курицей', 'Классический салат с куриным филе, пармезаном и соусом', 450.00, 250, 'uploads/dishes/1-caesar.jpg'),
    (1, 'Греческий салат', 'Свежие овощи с сыром фета и оливками', 380.00, 220, 'uploads/dishes/2-greek.jpg'),
    (1, 'Боул с киноа', 'Полезный боул с киноа, авокадо и овощами', 420.00, 0, 'uploads/dishes/11-bowl.jpg'),
    (1, 'Нисуаз', 'Французский салат с тунцом и яйцом', 390.00, 0, 'uploads/dishes/14-salad.jpg'),
    (2, 'Том Ям', 'Острый тайский суп с креветками на кокосовом молоке', 550.00, 300, 'uploads/dishes/3-tom-yam.jpg'),
    (2, 'Борщ', 'Традиционный русский суп со сметаной', 320.00, 350, 'uploads/dishes/4-borscht.jpg'),
    (2, 'Рамен', 'Японский суп с лапшой, свининой и яйцом', 480.00, 0, 'uploads/dishes/13-ramen.jpg'),
    (3, 'Стейк Рибай', 'Мраморная говядина с овощами гриль', 1200.00, 300, 'uploads/dishes/5-steak.jpg'),
    (3, 'Паста Карбонара', 'Итальянская паста с беконом и сливочным соусом', 480.00, 280, 'uploads/dishes/6-carbonara.jpg'),
    (3, 'Пицца Маргарита', 'Классическая итальянская пицца с моцареллой', 550.00, 0, 'uploads/dishes/12-pizza.jpg'),
    (3, 'Лосось с овощами', 'Запечённый лосось с сезонными овощами', 890.00, 0, 'uploads/dishes/15-fish.jpg'),
    (3, 'Куриный рулет', 'Куриный рулет с грибами и сыром', 520.00, 0, 'uploads/dishes/16-chicken.jpg'),
    (3, 'Бургер', 'Говяжий бургер с сыром и карамелизированным луком', 490.00, 0, 'uploads/dishes/18-burger.jpg'),
    (4, 'Тирамису', 'Итальянский десерт с маскарпоне', 350.00, 150, 'uploads/dishes/7-tiramisu.jpg'),
    (4, 'Чизкейк', 'Нью-йоркский чизкейк с ягодным соусом', 320.00, 180, 'uploads/dishes/8-cheesecake.jpg'),
    (4, 'Мороженое', 'Пломбир с ягодным топпингом', 280.00, 0, 'uploads/dishes/20-icecream.jpg'),
    (5, 'Лимонад', 'Домашний лимонад (лимон/апельсин/ягоды)', 180.00, 400, 'uploads/dishes/9-lemonade.jpg'),
    (5, 'Кофе', 'Эспрессо / Капучино / Латте', 200.00, 200, 'uploads/dishes/10-coffee.jpg'),
    (5, 'Смузи', 'Ягодный смузи с бананом и мятой', 250.00, 0, 'uploads/dishes/19-smoothie.jpg'),
    (6, 'Суши-сет', 'Ассорти из 8 видов суши и роллов', 950.00, 0, 'uploads/dishes/17-sushi.jpg'),
    (6, 'Холодец', 'Домашний холодец из говядины с хреном', 350.00, 0, 'uploads/dishes/21-kholodets.jpg'),
    (6, 'Окрошка', 'Классическая окрошка на квасе с овощами и колбасой', 280.00, 0, 'uploads/dishes/22-okroshka.jpg');

-- ========== 5. ДОЛЖНОСТИ ==========
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO positions (name) VALUES
    ('Шеф-повар'),
    ('Су-шеф'),
    ('Повар'),
    ('Кондитер'),
    ('Официант'),
    ('Старший официант'),
    ('Администратор'),
    ('Курьер'),
    ('Бариста'),
    ('Бармен'),
    ('Сомелье'),
    ('Менеджер');

-- ========== 6. ПЕРСОНАЛ ==========
CREATE TABLE personal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_id INT NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20),
    birth_date DATE,
    FOREIGN KEY (position_id) REFERENCES positions(id)
) ENGINE=InnoDB;

-- ========== 7. КОРЗИНА ==========
CREATE TABLE shopping_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dish_id INT NOT NULL,
    count INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========== 8. ЗАКАЗЫ ==========
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    personal_id INT DEFAULT NULL,
    address VARCHAR(255) NOT NULL,
    status ENUM('cart', 'pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    type VARCHAR(20) DEFAULT 'delivery',
    payment_status VARCHAR(20) DEFAULT 'unpaid',
    booking_id INT DEFAULT NULL,
    total_price DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (personal_id) REFERENCES personal(id)
) ENGINE=InnoDB;

-- ========== 9. СОСТАВ ЗАКАЗА ==========
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    count INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id)
) ENGINE=InnoDB;

-- ========== 10. НОВОСТИ ==========
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO news (title, description) VALUES
    ('Новое сезонное меню', 'Попробуйте наши новые летние блюда из свежих сезонных продуктов'),
    ('Скидка 20% на первый заказ', 'Для новых клиентов скидка на первый заказ через сайт'),
    ('Мастер-класс от шеф-повара', 'Научитесь готовить фирменные блюда нашего ресторана');

-- ========== 11. ОТЗЫВЫ ==========
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    text TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO reviews (name, text, rating) VALUES
    ('Анна', 'Очень вкусно! Обязательно вернусь ещё!', 5),
    ('Иван', 'Отличное место для ужина с семьёй.', 4),
    ('Мария', 'Лучший ресторан в городе!', 5);

-- ========== 12. БРОНИРОВАНИЯ ==========
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    guests INT NOT NULL DEFAULT 1,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    comment TEXT DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO bookings (name, phone, guests, booking_date, booking_time, status) VALUES
    ('Анна Петрова', '+79991234567', 2, CURDATE() + INTERVAL 1 DAY, '19:00', 'confirmed'),
    ('Иван Сидоров', '+79997654321', 4, CURDATE() + INTERVAL 2 DAY, '20:00', 'pending');

-- ========== 13. АКЦИИ ==========
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO promotions (title, description, start_date, end_date) VALUES
    ('Скидка 20% на первый заказ', 'Для новых клиентов скидка на первый заказ через сайт', CURDATE(), CURDATE() + INTERVAL 30 DAY),
    ('Бизнес-ланч за 350 ₽', 'С 12:00 до 15:00 в будние дни — комплексный обед', CURDATE(), NULL),
    ('Десерт в подарок', 'Фирменный десерт в подарок в день рождения', CURDATE(), NULL);

-- ========== 14. ОБРАТНАЯ СВЯЗЬ ПО ЗАКАЗАМ ==========
CREATE TABLE order_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    rating ENUM('like', 'dislike') NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========== 15. ОБРАТНАЯ СВЯЗЬ ПО БРОНИРОВАНИЯМ ==========
CREATE TABLE booking_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    rating ENUM('like', 'dislike') NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========== ТЕСТОВЫЙ АДМИН (Шеф-повар) ==========
-- Логин: +79990000001
-- Пароль: admin123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000001', 'Шеф-повар', 'Шеф-повар', '$2y$12$llzwV9kcLPH7qobUz48wje0Z5efr2SVHTYWvvZJkiX00gxOFZal06', 1);

-- ========== ТЕСТОВЫЙ ПОЛЬЗОВАТЕЛЬ ==========
-- Логин: +79990000002
-- Пароль: user123
INSERT INTO users (phone, password, access_rights_id) VALUES
    ('+79990000002', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 2);

-- ========== ТЕСТОВЫЕ СОТРУДНИКИ ==========
-- Су-шеф: +79990000003 / pass123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000003', 'Анна', 'Су-шеф', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 3);

-- Повар: +79990000004 / pass123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000004', 'Сергей', 'Повар', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 3);

-- Официант: +79990000005 / pass123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000005', 'Мария', 'Официант', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 3);

-- Старший официант: +79990000006 / pass123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000006', 'Дмитрий', 'Старший официант', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 3);

-- Кондитер: +79990000007 / pass123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000007', 'Елена', 'Кондитер', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 3);

-- Бариста: +79990000008 / pass123
INSERT INTO users (phone, name, position, password, access_rights_id) VALUES
    ('+79990000008', 'Алексей', 'Бариста', '$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu', 3);
