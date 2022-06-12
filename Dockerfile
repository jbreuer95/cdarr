FROM alpine:3.16.0

ENV PUID="1000" PGID="1000" TZ="Europe/London"

# Install base packages, php8, nginx, node, npm, composer, ffmpeg
RUN apk update && apk add --no-cache shadow bash curl \
    php8 php8-session php8-phar php8-dom php8-fpm php8-bcmath \
    php8-ctype php8-fileinfo php8-json php8-mbstring php8-openssl php8-xmlwriter \
    php8-pdo php8-pdo_sqlite php8-tokenizer php8-xml php8-sqlite3 php8-curl \
    nginx composer nodejs npm ffmpeg

# Install handbrake
RUN apk update && apk add --no-cache handbrake --repository="http://dl-cdn.alpinelinux.org/alpine/edge/testing"

# Install s6-overlay
ENV S6_OVERLAY_VERSION=3.1.0.1
ENV S6_CMD_WAIT_FOR_SERVICES_MAXTIME=0

ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-noarch.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-x86_64.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-symlinks-noarch.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-symlinks-noarch.tar.xz
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-symlinks-arch.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-symlinks-arch.tar.xz

# Add s6-overlay config
COPY docker/root/ /

# Configure nginx
RUN sed -i 's#user nginx;##g' /etc/nginx/nginx.conf && \
    sed -i 's#/var/log/nginx/error.log#/dev/stderr#g' /etc/nginx/nginx.conf && \
    sed -i 's#/var/log/nginx/access.log#/dev/stdout#g' /etc/nginx/nginx.conf

COPY docker/default.conf /etc/nginx/http.d/default.conf

# Configure PHP-fpm
RUN sed -i 's#;error_log = log/php8/error.log#error_log = /dev/stderr#g' /etc/php8/php-fpm.conf && \
    sed -i 's#user = nobody##g' /etc/php8/php-fpm.d/www.conf && \
    sed -i 's#group = nobody##g' /etc/php8/php-fpm.d/www.conf

# Add application
WORKDIR /app
# COPY . /app

# Run composer install to install the dependencies
# RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

# Build FE
# RUN npm install && npm run production && rm -rf node_modules

# Expose the port nginx is reachable on
EXPOSE 5656

# Hint usable volumes
VOLUME [ "/config", "/tv", "/movies" ]

# Init s6-overlay
ENTRYPOINT [ "/init" ]

