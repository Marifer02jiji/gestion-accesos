FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar proyecto
WORKDIR /var/www/html
COPY . .

# Instalar dependencias
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar nginx
RUN echo 'server {\n\
    listen 80;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \\.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
}' > /etc/nginx/sites-available/default

# Configurar supervisor
RUN echo '[supervisord]\n\
nodaemon=true\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]