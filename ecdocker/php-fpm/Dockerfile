FROM php:7.2.18-fpm

ADD ./expandcart.ini /usr/local/etc/php/conf.d/
ADD ./expandcart.pool.conf /usr/local/etc/php-fpm.d/

RUN apt-get update && apt-get install -y \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libmcrypt-dev \
	libpng-dev \
    curl

# Install extensions using the helper script provided by the base image
RUN docker-php-ext-install \
	zip \
	pdo_mysql \
	mysqli \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd

#RUN usermod -u 1000 www-data

WORKDIR /var/www/html

CMD ["php-fpm"]

EXPOSE 9000
