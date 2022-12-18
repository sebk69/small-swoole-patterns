FROM php:8.1-cli

ARG SWOOLE_PROJECT
ARG SWOOLE_VERSION

# install swoole
RUN pear config-set php_ini /usr/local/etc/php/php.ini
RUN apt-get update && \
    apt-get install -y libssl-dev

RUN apt-get update && \
    apt-get install -y  apt-utils
RUN apt-get update && \
    apt-get install -y libcurl4-openssl-dev && \
    docker-php-ext-install curl

RUN pecl install  -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="yes" enable-swoole-curl="yes"' $SWOOLE_PROJECT$SWOOLE_VERSION
RUN docker-php-ext-enable $SWOOLE_PROJECT

# install composer
RUN apt-get update && \
    apt-get install -y git zip
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin --filename=composer
RUN chmod 755 /usr/bin/composer

# system setup
RUN mkdir /usr/lib/small-swoole-patterns
WORKDIR /usr/lib/small-swoole-patterns

ENTRYPOINT sleep infinity