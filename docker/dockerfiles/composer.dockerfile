# Use the official Composer image with PHP 8.2 as a base
FROM composer:latest

# Set user permission
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Switch to the Composer user
USER laravel

# Set the working directory
WORKDIR /var/www/html

# Default command (optional)
CMD ["composer", "--ignore-platform-reqs"]
