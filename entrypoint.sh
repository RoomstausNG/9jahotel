#!/bin/sh
set -e
echo "Deploying application"

#Enter maintenance mode

(php artisan down --message='The website is been quickly updated. Please tey again in minutes.') || true
  #update codebase
    #clear every optimization
    php artisan optimize:clear
    # git pull origin 9jahotel
    # # git reset --hard origin/apis

    composer install --optimize-autoloader -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist --no-dev
    composer dump-autoload

    php artisan migrate --force

    npm install
    npm run build

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # script permissions
    chown -R 777 deploy.sh
    chmod 777 deploy.sh
    chmod -R 777 storage bootstrap/cache
    chown -R 777 storage bootstrap/cache

    # optimize the app
    php artisan optimize
#exit maintainance mode
php artisan up

echo "Website deployed"
