FROM php:8.3-fpm-alpine

# --------------- default env php.ini and fpm.conf values ---------------
ENV FPM_PM='dynamic' \
    FPM_PM_MAX_CHILDREN=50 \
    FPM_PM_MIN_SPARE_SERVERS=5 \
    FPM_PM_MAX_SPARE_SERVERS=10 \
    FPM_PM_MAX_REQUESTS=500 \
    PHP_INI_ERROR_REPORTING=22527 \
    PHP_INI_DISPLAY_ERRORS='Off' \
    PHP_INI_DISPLAY_STARTUP_ERRORS='Off' \
    PHP_INI_MEMORY_LIMIT='512M' \
    PHP_MAX_EXECUTION_TIME=30 \
    PHP_INI_UPLOAD_MAX_FILESIZE='35M' \
    PHP_INI_POST_MAX_SIZE='35M' \
    PHP_INI_REALPATH_CACHE_SIZE='4096K' \
    PHP_INI_REALPATH_CACHE_TTL=3600 \
    PHP_INI_OPCACHE_INTERNED_STRINGS_BUFFER=16 \
    PHP_INI_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_INI_OPCACHE_MAX_ACCELERATED_FILES=30000 \
    PHP_INI_CLI_OPCACHE_ENABLE_CLI=1

# Install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# --------------- Install packages ---------------
RUN apk --no-cache add bind-tools binutils bash tzdata nginx-mod-http-headers-more

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    mariadb-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    bash \
    git \
    unzip \
    curl

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    intl

# --------------- php-fpm configs ---------------
COPY ./docker/config/php/*.ini /usr/local/etc/php/conf.d/
COPY ./docker/config/php-fpm/*.conf /usr/local/etc/php-fpm.d/

COPY --chown=www-data app/composer.* ./

RUN composer install --no-scripts --no-autoloader

# ----------------- copy application code ------------------------
COPY --chown=www-data /app ./