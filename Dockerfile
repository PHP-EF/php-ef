ARG ALPINE_VERSION=3.20
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
  php83 \
  php83-ldap \
  php83-ctype \
  php83-sqlite3 \
  php83-pdo \
  php83-pdo_sqlite \
  php83-curl \
  php83-dom \
  php83-fileinfo \
  php83-fpm \
  php83-gd \
  php83-intl \
  php83-mbstring \
  php83-mysqli \
  php83-opcache \
  php83-openssl \
  php83-phar \
  php83-session \
  php83-tokenizer \
  php83-xml \
  php83-xmlreader \
  php83-xmlwriter \
  php83-simplexml \
  php83-posix \
  supervisor \
  redis \
  git

# Configure nginx - http
COPY Docker/config/nginx.conf /etc/nginx/nginx.conf
# Configure nginx - default server
COPY Docker/config/conf.d /etc/nginx/conf.d/

# Configure PHP-FPM
ENV PHP_INI_DIR=/etc/php83
COPY Docker/config/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY Docker/config/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Configure supervisord
COPY Docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody:nobody /var/www/html /run /var/lib/nginx /var/log/nginx /var/log/redis

# Configure Cron
RUN echo '* * * * * /usr/local/bin/php /var/www/html/inc/scheduler/scheduler.php' > /etc/crontabs/root

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