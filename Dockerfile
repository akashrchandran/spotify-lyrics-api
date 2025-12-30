# Use minimal Alpine-based PHP image
FROM php:8.3-alpine

# Install only required extensions and dependencies
RUN apk add --no-cache curl \
    && docker-php-ext-install opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure opcache for production
RUN { \
    echo 'opcache.memory_consumption=64'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /app

# Copy composer.json
COPY composer.json ./

# Install dependencies (no dev, optimized autoloader)
RUN COMPOSER_PROCESS_TIMEOUT=600 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --prefer-dist

# Copy source files
COPY api/ ./api/
COPY src/ ./src/

# Expose port
EXPOSE 8080

# Use PHP built-in server (smallest footprint)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "api"]
