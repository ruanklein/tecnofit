FROM php:8.4-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --optimize-autoloader

# RUN chown -R www-data:www-data /var/www/html \
#     && chmod -R 755 /var/www/html

CMD ["apache2-foreground"] 