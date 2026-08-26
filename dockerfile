# ============================================
# ESTÁGIO 1: BASE
# ============================================
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    libzip-dev \
    unzip \
    git \
    curl \
    ca-certificates \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

# CONFIGURAR PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# ============================================
# ESTÁGIO 2: TEST
# ============================================
FROM base AS test

COPY . .
RUN composer install --prefer-dist --no-interaction --no-progress
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php", "artisan", "test"]

# ============================================
# ESTÁGIO 3: PRODUCTION
# ============================================
FROM base AS production

# Copiar código
COPY . .

# Copiar .env de produção
COPY .env.production .env

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-progress \
    && composer clear-cache

# ============================================
# CRIAR TODOS OS DIRETÓRIOS NECESSÁRIOS
# ============================================
RUN mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && mkdir -p /tmp/laravel/cache \
    && mkdir -p /tmp/laravel/views \
    && mkdir -p /tmp/laravel/logs

# ============================================
# CRIAR ARQUIVO DE LOG E DAR PERMISSÕES (build time)
# ============================================
RUN touch /var/www/html/storage/logs/laravel.log \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 777 /tmp \
    && chmod 664 /var/www/html/storage/logs/laravel.log

# ============================================
# PERMISSÕES FINAIS
# ============================================
RUN chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /tmp

# ============================================
# ENTRYPOINT (gera APP_KEY e monta cache de config em runtime,
# depois que o container já tem as variáveis reais do compose)
# ============================================
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]