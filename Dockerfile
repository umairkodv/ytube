# Start from official PHP 8.1 Apache image
FROM php:8.1-apache

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    python3-dev \
    build-essential \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Show Python and pip versions (for verification)
RUN python3 --version && pip3 --version

# Upgrade pip (avoid externally-managed-environment error)
RUN pip3 install --upgrade pip --break-system-packages

# Install yt-dlp using pip
RUN pip3 install yt-dlp --break-system-packages

# Verify yt-dlp installed correctly
RUN yt-dlp --version

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files into container
COPY . .

# Create and set permissions for temp and logs folders
RUN mkdir -p /var/www/html/temp /var/www/html/logs \
    && chmod -R 777 /var/www/html/temp /var/www/html/logs

# Expose port 80 for HTTP
EXPOSE 80

# Run Apache in foreground
CMD ["apache2-foreground"]
