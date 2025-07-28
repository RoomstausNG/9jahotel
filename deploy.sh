#!/bin/bash
set -e

echo ">> Deploying application..."

# Store the current state
echo ">> Saving current state..."
git stash

# Fetch the latest changes
echo ">> Fetching latest changes..."
git fetch
git reset --hard origin/backoffice

# Pull the latest changes with a strategy to handle permission issues
echo ">> Force pulling latest changes..."

git pull --rebase --strategy-option=theirs
if [ $? -eq 0 ]; then
  echo "✓ SUCCESS: Git pull from backoffice branch"
else
  echo "✗ ERROR: Git pull from backoffice branch failed"
  # Try alternative approach if standard pull fails
  echo ">> Attempting to fix permissions before reset..."
  sudo find . -type f -name ".user.ini" -exec chmod 666 {} \; || find . -type f -name ".user.ini" -exec chmod 666 {} \; || true

  git reset --hard origin/backoffice
  if [ $? -eq 0 ]; then
    echo "✓ SUCCESS: Git reset to backoffice branch"
  else
    echo "✗ ERROR: Git force pull from backoffice branch failed"
    echo ">> Attempting with sudo..."
    sudo git reset --hard origin/backoffice || {
      echo "✗ ERROR: Git force pull from backoffice branch failed even with sudo"
      echo "Stopping script execution"
      exit 1
    }
  fi
fi

# Run remaining deployment steps
echo "✓ SUCCESS: Deployment is successfully!"

# Run remaining deployment steps
echo ">> Installing dependencies..."
composer install --no-dev --optimize-autoloader
composer dump-autoload

echo ">> Running migrations..."
php artisan migrate --force

echo ">> Clearing config..."
php artisan config:clear
echo ">> Clearing route..."
php artisan route:clear
echo ">> Clearing cache..."
php artisan cache:clear
echo ">> Clearing optimize..."
php artisan optimize:clear

echo ">> Setting permissions to 777..."
chmod -R 777 storage bootstrap/cache
chown -R 777 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage/bootstrap/cache

echo ">> Bringing application online..."
php artisan up

# Cache configuration for better performance
echo "⚡ Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo ">> Running hotel price update..."
php artisan update:price

echo "✓ SUCCESS: Deployment is successfully!"
