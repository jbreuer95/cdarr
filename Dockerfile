FROM alpine:3.12.0

ENV PUID="1000" PGID="1000" TZ="Europe/London"

# Install base packages, php7 and nginx
RUN apk update && apk add --no-cache shadow bash curl nginx \
    php7 php7-session php7-phar php7-dom php7-fpm php7-bcmath \
    php7-ctype php7-fileinfo php7-json php7-mbstring php7-openssl \
    php7-pdo php7-pdo_sqlite php7-tokenizer php7-xml php7-sqlite3

# Install handbrake, ffmpeg, node, yarn and composer
RUN apk update && apk add --no-cache handbrake ffmpeg composer nodejs yarn --repository="http://dl-cdn.alpinelinux.org/alpine/edge/testing"

# Install s6-overlay
RUN curl -fsSL "https://github.com/just-containers/s6-overlay/releases/latest/download/s6-overlay-amd64.tar.gz" | tar xzf - -C /

# Add s6-overlay config
COPY docker/root/ /

# Configure nginx
RUN sed -i 's#user nginx;##g' /etc/nginx/nginx.conf && \
    sed -i 's#/var/log/nginx/error.log#/dev/stderr#g' /etc/nginx/nginx.conf && \
    sed -i 's#/var/log/nginx/access.log#/dev/stdout#g' /etc/nginx/nginx.conf

# COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf

# Configure PHP-fpm
RUN sed -i 's#;error_log = log/php7/error.log#error_log = /dev/stderr#g' /etc/php7/php-fpm.conf && \
    sed -i 's#user = nobody##g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's#group = nobody##g' /etc/php7/php-fpm.d/www.conf

# Add application
WORKDIR /app
COPY . /app

# Run composer install to install the dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

# Build FE
RUN yarn install && yarn run production && rm -rf node_modules

# Expose the port nginx is reachable on
EXPOSE 5757

# Hint usable volumes
VOLUME [ "/config", "/tv", "/movies" ]

# Init s6-overlay
ENTRYPOINT [ "/init" ]

