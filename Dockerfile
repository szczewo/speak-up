FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql zip gd

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


WORKDIR /var/www/html


COPY . /var/www/html


RUN mkdir -p /var/www/html/var && \
    chown -R www-data:www-data /var/www/html/var \
    && chmod -R 775 /var/www/html/var

RUN composer install
RUN npm install

CMD ["apache2-foreground"]
