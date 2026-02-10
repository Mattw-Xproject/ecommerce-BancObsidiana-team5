# Usamos PHP 8.3 con Apache (Coincide con tu entorno local)
FROM php:8.3-apache

# 1. Instalar dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 2. Habilitar mod_rewrite de Apache (Vital para Laravel)
RUN a2enmod rewrite
# ... (después de instalar las dependencias con apt-get)
# Corregir el error de MPM: Deshabilitar event/worker y habilitar prefork

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite
RUN a2enmod headers

# Deshabilitar todos los MPMs excepto prefork
RUN a2dismod mpm_event mpm_worker mpm_prefork
RUN a2enmod mpm_prefork


# 3. Configurar el Document Root de Apache a la carpeta /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf | sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# 4. Instalar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Establecer directorio de trabajo
WORKDIR /var/www/html

# 1. Instalar Node.js 20 (Necesario para Vite 7+)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

    # Copiar archivos de paquetes
COPY package*.json ./

# FORZAR la instalación de los plugins de Tailwind v4
RUN npm install @tailwindcss/postcss postcss autoprefixer

# 2. Instalar dependencias de Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 3. Instalar dependencias de Node
COPY package*.json ./
# Luego instalar el resto y compilar
RUN npm install && npm run build

# 4. Copiar el resto del código
COPY . .

# 5. Compilar assets para producción
# Esto genera el archivo /public/build/manifest.json que Laravel necesita
RUN npm run build

# 7. Instalar dependencias de PHP (Optimizadas para producción)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 8. Dar permisos a las carpetas de almacenamiento
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Exponer el puerto 80 (Render inyecta la variable PORT, pero Apache escucha en 80 por defecto)
EXPOSE 80

# 10. Script de inicio para ejecutar migraciones antes de arrancar
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
