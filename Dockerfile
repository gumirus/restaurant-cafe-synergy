FROM php:8.2-cli

# Устанавливаем расширения MySQL и MySQL клиент
RUN docker-php-ext-install pdo pdo_mysql mysqli && \
    apt-get update && \
    apt-get install -y default-mysql-client && \
    rm -rf /var/lib/apt/lists/*

# Копируем проект
COPY . /app

# Рабочая директория
WORKDIR /app

# Делаем скрипт инициализации исполняемым
RUN chmod +x init-db.sh

# Railway даёт порт через переменную PORT
EXPOSE 8080

# Запускаем инициализацию БД, затем PHP сервер
CMD ["sh", "-c", "./init-db.sh && php -S 0.0.0.0:${PORT:-8080} router.php"]
