# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    zip

# Enable Apache modules
RUN a2enmod rewrite headers

# Set PHP configuration for OpenSIS
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/assets

# Keep install directory for future client installations
# RUN rm -rf /var/www/html/install

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf

# Create a health check file
RUN echo "<?php echo 'OK'; ?>" > /var/www/html/health.php

# Create startup script for dynamic port configuration
RUN echo '#!/bin/bash\n\
# Use PORT environment variable or default to 8080\n\
export PORT=${PORT:-8080}\n\
# Configure Apache to listen on the specified port\n\
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf\n\
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf\n\
# Ensure DocumentRoot is set correctly\n\
sed -i "s|DocumentRoot.*|DocumentRoot /var/www/html|" /etc/apache2/sites-available/000-default.conf\n\
# Start Apache\n\
apache2-foreground' > /usr/local/bin/start-apache.sh \
    && chmod +x /usr/local/bin/start-apache.sh

# Expose port (will be overridden by Cloud Run)
EXPOSE 8080

# Start Apache with dynamic port configuration
CMD ["/usr/local/bin/start-apache.sh"]