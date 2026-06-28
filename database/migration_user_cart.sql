-- =============================================
-- Миграция: таблица user_cart для серверной корзины
-- =============================================
CREATE TABLE IF NOT EXISTS user_cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_dish (user_id, dish_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fix: add missing avatar_data column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar_data LONGTEXT DEFAULT NULL AFTER avatar;
