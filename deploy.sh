#!/bin/sh
set -e
echo "Deploying application"

#Enter maintenance mode

(php artisan down --message='The website is been quickly updated. Please tey again in minutes.') || true
  #update codebase

    # git pull origin v1
    git fetch origin apis
    git reset --hard origin/apis

    composer install --optimize-autoloader -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist --no-dev
    composer dump-autoload

    php artisan migrate --force

    npm install
    npm run build

     # Clear cache
    # php artisan view:clear
    # php artisan event:clear
    # php artisan cache:clear
    # php artisan route:clear
    # php artisan config:clear

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan optimize

    # script permissions
    chown -R 777 deploy.sh
    chmod 777 deploy.sh
#exit maintainance mode
php artisan up

echo "Website deployed"
