# Use the official Nginx image as the base image
FROM nginx:stable-alpine

# Set the working directory for Nginx configuration
WORKDIR /etc/nginx/conf.d

# Copy your custom Nginx configuration file to the container
COPY nginx/default.conf .

#Rename the copied file to default
RUN mv default.conf default.conf

WORKDIR /var/www/html

COPY ../ .