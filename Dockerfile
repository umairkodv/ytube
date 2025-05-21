# Start with an official PHP image
FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Verify python3 and pip3 installation
RUN python3 --version && pip3 --version

# Install yt-dlp via pip
RUN pip3 install yt-dlp

# Enable mod_rewrite for Apache (if needed)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy your PHP files to the container's working directory
COPY . .

# Make temp and log directories writable
RUN mkdir -p /var/www/html/temp /var/www/html/logs \
    && chmod -R 777 /var/www/html/temp /var/www/html/logs

# Expose the port Apache runs on (default is 80)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
