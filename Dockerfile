# Start with the official PHP image
FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    python3-dev \
    build-essential \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Verify Python and pip installation
RUN python3 --version && pip3 --version

# Upgrade pip to latest version
RUN pip3 install --upgrade pip

# Install yt-dlp via pip
RUN pip3 install yt-dlp

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Ensure temp and logs directories exist and are writable
RUN mkdir -p /var/www/html/temp /var/www/html/logs \
    && chmod -R 777 /var/www/html/temp /var/www/html/logs

# Expose Apache port
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
