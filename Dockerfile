FROM php:8.2-cli

RUN apt-get update && apt-get install -y git curl libpng-dev libonig-dev libxml2-dev zip unzip nodejs npm

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev

COPY package.json package-lock.json ./
RUN npm install && npm run build

COPY . .
RUN php artisan key:generate --force || true

EXPOSE 10000

CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "10000"]