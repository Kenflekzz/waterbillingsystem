FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy ALL files first
COPY . .

# Copy .env.example to .env
RUN cp .env.example .env

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Install Node deps and build frontend (resources/ is now available)
RUN npm install && npm run build

# Run composer scripts after full app is present
RUN composer run-script post-autoload-dump || true

# Set correct permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD echo "APP_NAME=Laravel" > .env && \
    echo "APP_ENV=${APP_ENV}" >> .env && \
    echo "APP_KEY=${APP_KEY}" >> .env && \
    echo "APP_DEBUG=${APP_DEBUG}" >> .env && \
    echo "APP_URL=${APP_URL}" >> .env && \
    echo "DB_CONNECTION=${DB_CONNECTION}" >> .env && \
    echo "DB_HOST=${DB_HOST}" >> .env && \
    echo "DB_PORT=${DB_PORT}" >> .env && \
    echo "DB_DATABASE=${DB_DATABASE}" >> .env && \
    echo "DB_USERNAME=${DB_USERNAME}" >> .env && \
    echo "DB_PASSWORD=${DB_PASSWORD}" >> .env && \
    echo "MYSQL_ATTR_SSL_CA=${MYSQL_ATTR_SSL_CA}" >> .env && \
    echo "SESSION_DRIVER=file" >> .env && \
    echo "CACHE_STORE=file" >> .env && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan serve --host 0.0.0.0 --port 10000