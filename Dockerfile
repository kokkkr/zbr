FROM php:8.2-apache

# تثبيت امتداد MySQL
RUN docker-php-ext-install pdo pdo_mysql

# تفعيل mod_rewrite لو عندك .htaccess
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

# صلاحيات بسيطة (اختياري)
RUN chown -R www-data:www-data /var/www/html
