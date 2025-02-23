FROM caddy:2-alpine

WORKDIR /var/www/html

COPY index.php .

EXPOSE 80

COPY Caddyfile /etc/caddy/Caddyfile

RUN echo ":80 {" > /etc/caddy/Caddyfile && \
    echo "    root * /var/www/html" >> /etc/caddy/Caddyfile && \
    echo "    php_fastcgi 127.0.0.1:9000" >> /etc/caddy/Caddyfile && \
    echo "    file_server" >> /etc/caddy/Caddyfile && \
    echo "}" >> /etc/caddy/Caddyfile

FROM php:8-fpm-alpine

WORKDIR /var/www/html
COPY index.php .
