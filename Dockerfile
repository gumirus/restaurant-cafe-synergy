FROM php:8.2-cli

# Устанавливаем расширения MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Копируем проект
COPY . /app

# Рабочая директория
WORKDIR /app

# Railway даёт порт через переменную PORT
EXPOSE 8080

# router.php перенаправляет запросы
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} router.php"]
