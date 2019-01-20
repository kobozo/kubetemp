FROM firespring/apache2-php:7.2

RUN touch /etc/apache2/sites-enabled/settings.conf && \
    rm -f /var/www/html/*

ADD html/ /var/www/html
