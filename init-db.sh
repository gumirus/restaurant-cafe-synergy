#!/bin/sh
# =============================================
# ИНИЦИАЛИЗАЦИЯ БАЗЫ ДАННЫХ
# Импортирует дамп restaurant.sql в MySQL
# =============================================

MAX_RETRIES=15  # ~30 секунд таймаут
RETRY=0

echo "⏳ Ожидание MySQL (${MAX_RETRIES} попыток)..."
until mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ "$RETRY" -ge "$MAX_RETRIES" ]; then
        echo "⚠️ MySQL не доступен после ${MAX_RETRIES} попыток. Пропускаем инициализацию БД."
        echo "➡️ Запускаем PHP сервер без импорта..."
        exit 0
    fi
    sleep 2
done
echo "✅ MySQL доступен"

# Проверяем, есть ли уже таблицы
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME'" 2>/dev/null)

if [ "$TABLE_COUNT" = "0" ] || [ -z "$TABLE_COUNT" ]; then
    echo "📦 Импорт дампа restaurant.sql..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /app/database/restaurant.sql
    echo "✅ Дамп импортирован"
else
    echo "✅ Таблицы уже существуют ($TABLE_COUNT таблиц), проверяем миграции..."

    # Миграция: добавляем категорию "Холодные блюда", если её нет
    CAT_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM categories WHERE name='Холодные блюда'" 2>/dev/null)
    if [ "$CAT_EXISTS" = "0" ]; then
        echo "📦 Миграция: добавляем категорию 'Холодные блюда'..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            INSERT INTO categories (name) VALUES ('Холодные блюда');
            SET @cat_id = LAST_INSERT_ID();
            INSERT INTO dishes (category_id, name, description, price, weight) VALUES
                (@cat_id, 'Суши-сет', 'Набор из 8 видов суши и роллов с лососем, тунцом и креветкой', 950.00, 350),
                (@cat_id, 'Холодец', 'Домашний холодец из говядины с хреном и горчицей', 350.00, 250),
                (@cat_id, 'Окрошка', 'Классическая окрошка на квасе с говядиной', 280.00, 300);
        " 2>/dev/null
        echo "✅ Миграция: категория и блюда добавлены"
    else
        echo "✅ Миграций не требуется"
    fi
fi
