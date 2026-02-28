FROM php:8.2-cli

# تثبيت MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . /app

# Railway بيبعت PORT متغير
CMD php -S 0.0.0.0:$PORT -t .
