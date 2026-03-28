FROM php:8.2-apache

# Установка расширений для SQLite
RUN docker-php-ext-install pdo_sqlite

# Включение mod_rewrite
RUN a2enmod rewrite

# Копирование файлов
COPY . /var/www/html/

# Настройка прав на папку с базой данных
RUN mkdir -p /var/www/html/database
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 777 /var/www/html/database

# Настройка Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
