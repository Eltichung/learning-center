FROM serversideup/php:8.4-fpm-nginx

# Cài driver PostgreSQL (cần cho Render Postgres)
USER root
RUN install-php-extensions pdo_pgsql pgsql
USER www-data

# Copy mã nguồn vào webroot, gán quyền cho www-data
COPY --chown=www-data:www-data . /var/www/html

# Cài dependencies production
RUN composer install --no-dev --optimize-autoloader --no-interaction
