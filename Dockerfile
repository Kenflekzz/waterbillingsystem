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

# Create empty .env file
RUN touch .env

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Install Node deps and build frontend (resources/ is now available)
RUN npm install && npm run build

# Run composer scripts after full app is present
RUN composer run-script post-autoload-dump || true

# Set correct permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host 0.0.0.0 --port 10000