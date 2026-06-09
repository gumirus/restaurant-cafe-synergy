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
    name VARCHAR(50) NOT NULL UNIQUE  -- ADMIN / USER
) ENGINE=InnoDB;

INSERT INTO access_rights (name) VALUES ('ADMIN'), ('USER');

-- ========== 2. ПОЛЬЗОВАТЕЛИ ==========
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    access_rights_id INT NOT NULL DEFAULT 2,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_rights_id) REFERENCES access_rights(id)
) ENGINE=InnoDB;

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
    ('Напитки');

-- ========== 4. БЛЮДА ==========
CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    weight INT DEFAULT 0,          -- вес в граммах
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

INSERT INTO dishes (category_id, name, description, price, weight) VALUES
    (1, 'Цезарь с курицей', 'Классический салат с куриным филе, пармезаном и соусом', 450.00, 250),
    (1, 'Греческий салат', 'Свежие овощи с сыром фета и оливками', 380.00, 220),
    (2, 'Том Ям', 'Острый тайский суп с креветками на кокосовом молоке', 550.00, 300),
    (2, 'Борщ', 'Традиционный русский суп со сметаной', 320.00, 350),
    (3, 'Стейк Рибай', 'Мраморная говядина с овощами гриль', 1200.00, 300),
    (3, 'Паста Карбонара', 'Итальянская паста с беконом и сливочным соусом', 480.00, 280),
    (4, 'Тирамису', 'Итальянский десерт с маскарпоне', 350.00, 150),
    (4, 'Чизкейк', 'Нью-йоркский чизкейк с ягодным соусом', 320.00, 180),
    (5, 'Лимонад', 'Домашний лимонад (лимон/апельсин/ягоды)', 180.00, 400),
    (5, 'Кофе', 'Эспрессо / Капучино / Латте', 200.00, 200);

-- ========== 5. ДОЛЖНОСТИ ==========
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO positions (name) VALUES
    ('Шеф-повар'),
    ('Повар'),
    ('Официант'),
    ('Администратор'),
    ('Курьер');

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
    status ENUM('new', 'processing', 'delivering', 'completed', 'cancelled') DEFAULT 'new',
    total_price DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    guests INT NOT NULL DEFAULT 1,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    comment TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- ========== ТЕСТОВЫЙ АДМИН ==========
-- Логин: +79990000001
-- Пароль: admin123
INSERT INTO users (phone, password, access_rights_id) VALUES
    ('+79990000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- ========== ТЕСТОВЫЙ ПОЛЬЗОВАТЕЛЬ ==========
-- Логин: +79990000002
-- Пароль: user123
INSERT INTO users (phone, password, access_rights_id) VALUES
    ('+79990000002', '$2y$10$D9GXqB4s5m6n7o8p9q0r1s2t3u4v5w6x7y8z9a0b1c2d3e4f5g6h7i8j9k', 2);
