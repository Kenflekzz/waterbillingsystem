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

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Install Node dependencies and build frontend
RUN npm ci && npm run build

# Run post-autoload scripts safely
RUN composer run-script post-autoload-dump || true

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000


CMD env | grep -E "^(APP_|DB_|MYSQL_|SESSION_|CACHE_|CLOUDINARY_|IOT_|MOCEAN_|MAIL_|RESEND_)" > /app/.env && \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan storage:link && \
    php artisan serve --host 0.0.0.0 --port 10000