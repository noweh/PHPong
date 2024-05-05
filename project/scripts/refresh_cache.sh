#!/usr/bin/env bash

php -d memory_limit=-1 artisan cache:clear
php -d memory_limit=-1 artisan config:clear
php -d memory_limit=-1 artisan config:cache
php -d memory_limit=-1 artisan route:clear
php -d memory_limit=-1 artisan route:cache

APP_URL=$(grep APP_URL .env | cut -d '=' -f2)
APP_ENV=$(grep APP_ENV .env | cut -d '=' -f2)
OPCACHE_PORT=$(grep OPCACHE_PORT .env | cut -d '=' -f2)

if [ $APP_ENV != "local" ]
then
  php ~/cachetool.phar opcache:reset --fcgi=127.0.0.1:$OPCACHE_PORT
  printf "\033[1;32mOPCACHE cleaned.\033[0m\n"
fi
