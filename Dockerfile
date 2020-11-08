FROM alpine:3.12

ENV PUID=1000
ENV PGID=1000
ENV TZ=Europe/London

# Install packages
RUN apk update && apk --no-cache add php7 php7-session php7-phar php7-dom php7-fpm php7-bcmath php7-ctype php7-fileinfo php7-json \
    php7-mbstring php7-openssl php7-pdo php7-pdo_sqlite php7-tokenizer php7-xml php7-sqlite3 nginx supervisor curl su-exec

# Install handbrake and ffmpeg
RUN apk update && apk add --no-cache handbrake ffmpeg composer --repository="http://dl-cdn.alpinelinux.org/alpine/edge/testing"

# Remove default server definition
RUN rm /etc/nginx/conf.d/default.conf

# Configure nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY docker/fpm-pool.conf /etc/php7/php-fpm.d/www.conf
COPY docker/php.ini /etc/php7/conf.d/custom.ini

# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup document root
RUN mkdir -p /var/www

# Add application
WORKDIR /var/www
COPY . /var/www/

# Run composer install to install the dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

# Expose the port nginx is reachable on
EXPOSE 5757

VOLUME [ "/config", "/tv", "/movies" ]

# Start the init script on running the container
ENTRYPOINT ["/var/www/docker/init.sh"]
