ARG ALPINE_VERSION=3.24
FROM alpine:${ALPINE_VERSION}
LABEL maintainer="TehMuffinMoo" \
      description="PHP and NGINX Dockerfile to support PHP-EF."
# Setup document root
WORKDIR /var/www/html

# Install packages and remove default server definition
RUN apk add --no-cache \
  curl \
  composer \
  nginx \
  php85 \
  php85-ldap \
  php85-ctype \
  php85-sqlite3 \
  php85-pdo \
  php85-pdo_sqlite \
  php85-curl \
  php85-dom \
  php85-fileinfo \
  php85-fpm \
  php85-gd \
  php85-intl \
  php85-mbstring \
  php85-mysqli \
  php85-openssl \
  php85-phar \
  php85-session \
  php85-tokenizer \
  php85-xml \
  php85-xmlreader \
  php85-xmlwriter \
  php85-simplexml \
  php85-posix \
  supervisor \
  redis \
  git \
  busybox-suid

# Configure nginx - http
COPY Docker/config/nginx.conf /etc/nginx/nginx.conf
# Configure nginx - default server
COPY Docker/config/conf.d /etc/nginx/conf.d/
# Copy nginx proxy snippet
COPY Docker/config/proxy.conf /etc/nginx/proxy.conf

# Configure PHP-FPM
ENV PHP_INI_DIR=/etc/php85
COPY Docker/config/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY Docker/config/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Configure supervisord
COPY Docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody:nobody /var/www/html /run /var/lib/nginx /var/log/nginx /var/log/redis

# Configure Cron
# Install Supercronic
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.33/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8 \
    SUPERCRONIC=supercronic-linux-amd64

RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

# Configure Crontab
RUN mkdir /supercronic
RUN echo '* * * * * /usr/bin/php /var/www/html/inc/scheduler/scheduler.php' > /supercronic/crontab
RUN chown -R nobody:nogroup /supercronic /usr/local/bin/supercronic

# Switch to use a non-root user from here on
USER nobody

# Copy PHP-EF
COPY --chown=nobody CHANGELOG.md /var/www/html/
COPY --chown=nobody *.php /var/www/html/
COPY --chown=nobody composer.json /var/www/html/
COPY --chown=nobody api /var/www/html/api
COPY --chown=nobody assets /var/www/html/assets
COPY --chown=nobody files /var/www/html/files
COPY --chown=nobody inc /var/www/html/inc
COPY --chown=nobody pages /var/www/html/pages

# Configure Redis
COPY Docker/config/redis.conf /etc/redis/redis.conf

# Add entrypoint script
COPY --chown=nobody Docker/config/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Composer Update
RUN composer update

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping || exit 1

# Register Entry Point
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
