FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libcurl4-openssl-dev libonig-dev libpng-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring curl gd \
    && a2enmod rewrite headers \
    && printf 'ServerName localhost\n<Directory /var/www/html>\n  AllowOverride All\n  Require all granted\n</Directory>\n' > /etc/apache2/conf-available/deus-com-nos.conf \
    && a2enconf deus-com-nos \
    && rm -rf /var/lib/apt/lists/*

COPY ./docker/php-settings.ini /usr/local/etc/php/conf.d/90-deus-com-nos.ini
COPY ./deus-com-nos-php/ /var/www/html/
RUN mkdir -p /var/www/html/assets/products/uploads \
    && chown -R www-data:www-data /var/www/html/assets/products/uploads

WORKDIR /var/www/html
EXPOSE 80
