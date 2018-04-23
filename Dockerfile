FROM php:5.6-cli-alpine

# link: https://pkgs.alpinelinux.org/contents?file=mcrypt.h&path=&name=&branch=edge
RUN apk add --no-cache libmcrypt libmcrypt-dev bash

RUN docker-php-ext-install mcrypt

ADD ./ /root
WORKDIR /root

CMD php codificadorBasicoSARA.php
