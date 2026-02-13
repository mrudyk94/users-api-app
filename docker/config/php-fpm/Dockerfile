FROM php:8.3-fpm-alpine

# --------------- default env php.ini and fpm.conf values ---------------
ENV FPM_PM='dynamic' \
    FPM_PM_MAX_CHILDREN=50 \
    FPM_PM_START_SERVERS=5 \
    FPM_PM_MIN_SPARE_SERVERS=5 \
    FPM_PM_MAX_START_SERVERS=35 \
    FPM_PM_MAX_REQUESTS=500 \
    PHP_INI_ZEND_EXCEPTION_IGNORE_ARGS='On' \
    PHP_INI_ZEND_EXCEPTION_STRING_PARAM_MAX_LEN=0 \
    PHP_INI_ERROR_REPORTING=22527 \
    PHP_INI_DISPLAY_ERRORS='Off' \
    PHP_INI_DISPLAY_STARTUP_ERRORS='Off' \
    PHP_INI_MYSQLND_COLLECT_MEMORY_STATISTICS='Off' \
    PHP_INI_ZEND_ASSERTIONS=-1 \
    PHP_INI_MEMORY_LIMIT='512M' \
    PHP_MAX_EXECUTION_TIME=30 \
    PHP_INI_UPLOAD_MAX_FILESIZE='35M' \
    PHP_INI_POST_MAX_SIZE='35M' \
    PHP_INI_REALPATH_CACHE_SIZE='4096K' \
    PHP_INI_REALPATH_CACHE_TTL=3600 \
    PHP_INI_OPCACHE_INTERNED_STRINGS_BUFFER=16 \
    PHP_INI_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_INI_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_INI_OPCACHE_MAX_ACCELERATED_FILES=30000 \
    PHP_INI_OPCACHE_JIT_BUFFER_SIZE=0 \
    PHP_INI_CLI_OPCACHE_ENABLE_CLI=1 \
    PHP_INI_CLI_OPCACHE_FILE_CACHE_ONLY=1

# Install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# --------------- Install packages ---------------
RUN apk --no-cache add bind-tools binutils bash tzdata nginx-mod-http-headers-more

# --------------- php-fpm configs ---------------
COPY ./docker/config/php/zzz-php.ini /usr/local/etc/php/conf.d/
COPY ./docker/config/php-fpm/*.conf /usr/local/etc/php-fpm.d/

COPY --chown=www-data app/composer.* ./

RUN composer install --no-scripts --no-autoloader

# ------------------------ setup s6 supervisor ------------------------
# copy services start scripts, fix permissions scripts, pre init scripts
COPY ./docker/s6-supervisor /etc

# ----------------- copy application code ------------------------
COPY --chown=www-data /app ./

RUN composer dump-autoload --classmap-authoritative

# ------------------------ expose ports, etc ------------------------
EXPOSE 9000
CMD ["php-fpm", "-F"]