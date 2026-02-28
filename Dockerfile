FROM php:8.2-apache

# حل مشكلة More than one MPM loaded
RUN a2dismod mpm_event || true
RUN a2enmod mpm_prefork

# تثبيت MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql

# تفعيل rewrite
RUN a2enmod rewrite

# السماح بـ .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
