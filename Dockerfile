FROM php:8.2-cli

WORKDIR /app

# System deps + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo pdo_mysql \
 && rm -rf /var/lib/apt/lists/*

# Copy project
COPY . /app

# Install composer deps (if vendor not committed)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader || true

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT} server.php"]
