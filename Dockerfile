FROM php:8.2-cli

# Устанавливаем расширения MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Копируем проект
COPY . /app

# Рабочая директория
WORKDIR /app

# PHP built-in server с роутером
EXPOSE 8000

# router.php перенаправляет запросы
CMD ["php", "-S", "0.0.0.0:8000", "router.php"]
