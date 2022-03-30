FROM php:7.2-fpm-alpine
 
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
    && apk update \
    && apk add libpng-dev freetype-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/  \
    && docker-php-ext-install -j$(nproc) gd

COPY ./img.php /app/img.php
WORKDIR /app

CMD ["/bin/sh", "-c", "php img.php"]