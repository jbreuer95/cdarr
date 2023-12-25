FROM alpine:3.18

ENV PUID="1000" PGID="1000" TZ="Europe/London"

# Install base packages, php8.2, nginx, node, npm, ffmpeg
RUN apk update && apk add --no-cache shadow bash \
    php82 php82-fpm php82-phar php82-mbstring php82-openssl php82-curl \
    php82-session php82-fileinfo php82-tokenizer php82-dom \
    php82-pdo php82-pdo_sqlite php82-pcntl php82-posix php82-ctype php82-xml php82-intl \
    php82-opcache \
    nginx nodejs npm ffmpeg

# Install official composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG S6_OVERLAY_VERSION=3.1.5.0
ENV S6_CMD_WAIT_FOR_SERVICES_MAXTIME=0

ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-noarch.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-x86_64.tar.xz /tmp
RUN tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz

COPY --chmod=755 docker/etc/s6-overlay /etc/s6-overlay

# Create a symbolic link for php and php-fpm
RUN ln -s /usr/bin/php82 /usr/bin/php && ln -s /usr/sbin/php-fpm82 /usr/sbin/php-fpm

# Configure nginx
RUN sed -i 's#user nginx;##g' /etc/nginx/nginx.conf && \
    sed -i 's#/var/log/nginx/error.log#/dev/stderr#g' /etc/nginx/nginx.conf && \
    sed -i 's#/var/log/nginx/access.log#/dev/stdout#g' /etc/nginx/nginx.conf

# Configure PHP-fpm
RUN sed -i 's#;error_log = log/php82/error.log#error_log = /dev/stderr#g' /etc/php82/php-fpm.conf && \
    sed -i 's#user = nobody##g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's#group = nobody##g' /etc/php82/php-fpm.d/www.conf && \
    sed -i 's#;clear_env = no#clear_env = no#g' /etc/php82/php-fpm.d/www.conf

# Install nginx config
COPY docker/etc/s6-overlay/scripts/default.conf /etc/nginx/http.d/default.conf

# create abc user and make our folders
RUN \
  mkdir -p /app && \
  mkdir -p /config && \
  groupadd -g $PGID abc && \
  useradd -u $PUID -g $PGID -d /app abc && \
  chown -R abc:abc /app && \
  chown -R abc:abc /config

WORKDIR /app
COPY --chown=abc:abc . /app

# Run composer install to install the dependencies
RUN su abc -c 'composer install --optimize-autoloader --no-interaction --no-progress --no-dev --no-cache && rm -rf .composer'

# Build FE
RUN su abc -c 'npm install && npm run build && rm -rf node_modules && rm -rf .npm'

# Expose the port nginx is reachable on
EXPOSE 5656

# Hint usable volumes
VOLUME [ "/config", "/tv", "/movies" ]

ENTRYPOINT ["/init"]
