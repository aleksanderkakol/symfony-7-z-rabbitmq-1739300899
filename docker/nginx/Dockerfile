FROM nginx:1.27.4

ENV TIMEZONE=Europe/Warsaw
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone

RUN apt-get update && apt-get install -y \
    openssl

COPY nginx.conf /etc/nginx/
COPY symfony.ssl.conf /etc/nginx/conf.d/

RUN rm /etc/nginx/conf.d/default.conf \
&& echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data

RUN openssl req -x509 -newkey rsa:4086 \
    -keyout /etc/ssl/private/myssl.key -out /etc/ssl/certs/myssl.crt \
    -days 3650 -nodes -sha256 \
    -subj "/C=PL/ST=City/L=Location/O=localhost/OU=auth-code-sender.localhost/CN=auth-code-sender.localhost"