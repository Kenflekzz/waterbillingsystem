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

# Copy composer files first
COPY composer.json composer.lock ./

# Install PHP dependencies WITHOUT running scripts
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Copy package files and install Node deps
COPY package.json package-lock.json ./
RUN npm install && npm run build

# Copy rest of the app
COPY . .

# Now run composer scripts after full app is present
RUN composer run-script post-autoload-dump || true

# Generate app key at runtime (not build time)
EXPOSE 10000

CMD php artisan key:generate --force && php artisan serve --host 0.0.0.0 --port 10000