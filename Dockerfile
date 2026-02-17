# Usamos PHP 8.3 con Apache
FROM php:8.3-apache

# 1. Instalar dependencias del sistema, extensiones PHP y Node.js 20
# (Hacemos todo en un solo RUN para reducir capas y peso de la imagen)
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
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Configuración de Apache
# Habilitar mod_rewrite y headers
RUN a2enmod rewrite headers

# Configurar Document Root a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf | sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# 3. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Establecer directorio de trabajo
WORKDIR /var/www/html

# -----------------------------------------------------------------------------
# FASE DE DEPENDENCIAS (Aprovechamos la caché de Docker)
# -----------------------------------------------------------------------------

# 5. Instalar dependencias de Composer PRIMERO
COPY composer.json composer.lock ./
# IMPORTANTE: Quitamos '--no-dev' para que Faker se instale y funcionen los Seeders
RUN composer install --optimize-autoloader --no-scripts

# 6. Instalar dependencias de Node.js
COPY package*.json ./
# Forzamos la instalación de plugins necesarios para Tailwind v4
RUN npm install @tailwindcss/postcss postcss autoprefixer
RUN npm install

# -----------------------------------------------------------------------------
# FASE DE CONSTRUCCIÓN
# -----------------------------------------------------------------------------

# 7. Copiar TODO el código fuente
# (Esto se hace antes del build para que Vite encuentre los archivos)
COPY . .

# 8. Compilar Assets (Vite)
# Genera public/build/manifest.json
RUN npm run build

# 9. Permisos y Estructura de carpetas
# Creamos la carpeta database por si no existe para SQLite
RUN mkdir -p database storage/framework/sessions storage/framework/views storage/framework/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# 10. Exponer puerto 80
EXPOSE 80

# 10. Script de inicio para ejecutar migraciones antes de arrancar
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
