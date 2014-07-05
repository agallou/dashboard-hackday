#!/bin/bash -e

if [ ! -d /var/www ]; then
echo 'No application found in /var/www'
exit 1;
fi

if [ ! -z "$GITHUB_TOKEN" ]; then
    echo "env[GITHUB_TOKEN] = '`echo $GITHUB_TOKEN`'" >> /etc/php5/fpm/php-fpm.conf;
fi

if [ ! -d vendor ]; then
    composer install
fi

exec svscan /srv/services
