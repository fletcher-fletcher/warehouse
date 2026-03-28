FROM php:8.2-apache

# Установка SQLite3 и расширения
RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Включение mod_rewrite
RUN a2enmod rewrite

# Копирование файлов
COPY . /var/www/html/

# Создание папки для базы данных
RUN mkdir -p /var/www/html/database && \
    chown -R www-data:www-data /var/www/html/ && \
    chmod -R 777 /var/www/html/database

# Настройка Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
