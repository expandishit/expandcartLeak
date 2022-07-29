FROM alpine:3.13

LABEL Maintainer="Sameh Nabil <sameh@expandcart>" \
      Description="expandcart production Docker Container with Nginx 1.18 & PHP-FPM 7.4 based on Alpine Linux."

RUN apk --no-cache add php7 php7-fpm php7-opcache php7-mysqli php7-json php7-openssl php7-curl \
    php7-zlib php7-xml php7-phar php7-intl php7-dom php7-xmlreader php7-simplexml php7-ctype php7-session \
    php7-mbstring php7-gd php7-ldap nginx supervisor curl php7-pecl-redis && \
    rm /etc/nginx/conf.d/default.conf && \
    mkdir -p /var/www/expandcart && \
    # Make sure files/folders needed by the processes are accessable when they run under the nobody user
    chown -R nobody.nobody /var/www/expandcart && \
    chown -R nobody.nobody /run && \
    chown -R nobody.nobody /var/lib/nginx && \
    chown -R nobody.nobody /var/log/nginx

# Configure nginx
COPY docker/prod-nginx/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY docker/prod-nginx/fpm-pool.conf /etc/php7/php-fpm.d/www.conf
COPY docker/prod-nginx/php.ini /etc/php7/conf.d/custom.ini

# Configure supervisord
COPY docker/prod-nginx/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Switch to use a non-root user from here on
USER nobody

# Add application
#VOLUME /var/www/expandcart/
WORKDIR /var/www/expandcart
#COPY --chown=nobody . /var/www/expandcart/
COPY --chown=nobody ./docker/prod-nginx/Config/ /var/www/expandcart/Config/

# Expose the port nginx is reachable on
EXPOSE 8070

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8071/fpm-ping