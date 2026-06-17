FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
     && apt-get install -y nodejs

# install node dependencies for playwright
RUN npm install -g playwright

# install browsers + system deps
RUN npx playwright install --with-deps

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy startup script into image
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Make it executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Start using the script
ENTRYPOINT ["docker-entrypoint.sh"]

# Launch PHP-FPM
CMD ["php-fpm"]
