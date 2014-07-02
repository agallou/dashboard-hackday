FROM stackbrew/ubuntu:saucy
 
ENV DEBIAN_FRONTEND noninteractive
 
RUN apt-get update -y
RUN apt-get install -y \
 daemontools nginx curl \
 php5-cli php5-json php5-fpm php5-intl php5-curl

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php5/fpm/php-fpm.conf
RUN sed -e 's/;listen\.owner/listen.owner/' -i /etc/php5/fpm/pool.d/www.conf
RUN sed -e 's/;listen\.group/listen.group/' -i /etc/php5/fpm/pool.d/www.conf
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf

ADD docker/services/ /srv/services
ADD . /var/www

RUN if [ ! -z "$GITHUB_TOKEN" ]; then echo "env[GITHUB_TOKEN] = '`echo $GITHUB_TOKEN`'" >> /etc/php5/fpm/php-fpm.conf; fi

ADD docker/entrypoint.sh /usr/local/bin/entrypoint.sh
ADD docker/vhost.conf /etc/nginx/sites-enabled/default
 
WORKDIR /var/www

VOLUME ["/var/www"]

EXPOSE 80
 
CMD ["/usr/local/bin/entrypoint.sh"]
