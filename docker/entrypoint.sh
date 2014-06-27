#!/bin/bash -e

if [ ! -d /var/www ]; then
echo 'No application found in /var/www'
exit 1;
fi

if [ ! -d vendor ]; then
    composer install
fi
 
exec svscan /srv/services
