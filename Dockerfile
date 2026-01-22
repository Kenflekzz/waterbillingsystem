FROM php:8.2-fpm

# -----------------------------
# System dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Node.js 20 (REQUIRED by Laravel Echo)
# -----------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Composer
# -----------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------
# Create non-root user
# -----------------------------
ARG USER=user
ARG UID=1000

RUN useradd -G www-data,root -u $UID -d /home/$USER $USER \
    && mkdir -p /home/$USER/.composer \
    && chown -R $USER:$USER /home/$USER

# -----------------------------
# App directory + permissions
# -----------------------------
WORKDIR /var/www

RUN chown -R $USER:www-data /var/www

# -----------------------------
# Switch to non-root user
# -----------------------------
USER $USER

# -----------------------------
# Copy source & install PHP deps
# -----------------------------
COPY --chown=$USER:www-data . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000
