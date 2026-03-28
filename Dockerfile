FROM php:8.2-apache

# Установка расширений
RUN docker-php-ext-install pdo_mysql

# Включение mod_rewrite
RUN a2enmod rewrite

# Копирование файлов
COPY . /var/www/html/

# Настройка прав
RUN chown -R www-data:www-data /var/www/html/

# Настройка Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
