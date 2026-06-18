# Stage 1: build CSS/JS bằng Node
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: app PHP
FROM serversideup/php:8.4-fpm-nginx
USER root
RUN install-php-extensions pdo_pgsql pgsql
USER www-data
COPY --chown=www-data:www-data . /var/www/html
COPY --chown=www-data:www-data --from=frontend /app/public/build /var/www/html/public/build
RUN composer install --no-dev --optimize-autoloader --no-interaction
