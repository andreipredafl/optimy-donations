#!/bin/bash

if [ ! -f "/var/www/.env" ]; then
    cp /var/www/.env.example /var/www/.env
fi

sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' /var/www/.env
sed -i 's|DB_DATABASE=.*|DB_DATABASE=/var/www/database/database.sqlite|' /var/www/.env
sed -i '/DB_HOST=/d; /DB_PORT=/d; /DB_USERNAME=/d; /DB_PASSWORD=/d' /var/www/.env

APP_KEY=$(grep "^APP_KEY=" /var/www/.env | cut -d'=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

if [ ! -f "/var/www/database/database.sqlite" ]; then
    touch /var/www/database/database.sqlite
    chown www-data:www-data /var/www/database/database.sqlite
fi

php artisan migrate --force

exec "$@" 