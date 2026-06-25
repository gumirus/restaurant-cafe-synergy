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
    echo "✅ Таблицы уже существуют ($TABLE_COUNT таблиц), пропускаем импорт"
fi
