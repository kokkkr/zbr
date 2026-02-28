FROM php:8.2-cli

# تثبيت pdo mysql
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . /app

# Railway بيديك PORT في env
CMD php -S 0.0.0.0:$PORT -t /app
