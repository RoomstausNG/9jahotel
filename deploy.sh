#!/bin/bash
set -e

echo ">> Deploying application..."

# Store the current state
echo ">> Saving current state..."
git stash

# Fetch the latest changes
echo ">> Fetching latest changes..."
git fetch
git reset --hard origin/dashboard

# Pull the latest changes with a strategy to handle permission issues
echo ">> Force pulling latest changes..."
# First try to fix the problematic file permission
echo ">> Attempting to fix .user.ini permission..."
if [ -f "public/.user.ini" ]; then
  sudo chmod 666 public/.user.ini || chmod 666 public/.user.ini || true
  echo "Changed permissions on public/.user.ini"
fi

git pull --rebase --strategy-option=theirs
if [ $? -eq 0 ]; then
  echo "✓ SUCCESS: Git pull from dashboard branch"
else
  echo "✗ ERROR: Git pull from dashboard branch failed"
  # Try alternative approach if standard pull fails
  echo ">> Attempting to fix permissions before reset..."
  sudo find . -type f -name ".user.ini" -exec chmod 666 {} \; || find . -type f -name ".user.ini" -exec chmod 666 {} \; || true

  git reset --hard origin/dashboard
  if [ $? -eq 0 ]; then
    echo "✓ SUCCESS: Git reset to dashboard branch"
  else
    echo "✗ ERROR: Git force pull from dashboard branch failed"
    echo ">> Attempting with sudo..."
    sudo git reset --hard origin/dashboard || {
      echo "✗ ERROR: Git force pull from dashboard branch failed even with sudo"
      echo "Stopping script execution"
      exit 1
    }
  fi
fi

# Run remaining deployment steps
echo ">> Installing dependencies..."
composer install --no-dev --optimize-autoloader
echo ">> Running migrations..."
php artisan migrate --force

# Ensure storage files aren't pushed to git
echo ">> Checking .gitignore for storage entries..."
if ! grep -q "storage/\*" .gitignore; then
  echo "storage/*" >> .gitignore
  echo "!storage/.gitignore" >> .gitignore
  echo "✓ Added storage directory to .gitignore"
fi

# Use optimize:clear instead of individual clear commands
echo ">> Clearing all caches..."
php artisan optimize:clear

# Fix storage permissions - Changed to 777 for full access
echo ">> Setting permissions to 775..."
sudo chmod -R 775 storage bootstrap/cache 2>/dev/null || chmod -R 775 storage bootstrap/cache

# Set proper permissions and ownership for web server
echo ">> Setting proper ownership..."
sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage bootstrap/cache

# Bring the application back online
echo ">> Bringing application online..."

echo ">> Running hotel price update..."
php artisan update:price

php artisan optimize:cache

chown -R 777 storage bootstrap/cache
chmod -R 777 storage bootstrap/cache
php artisan up

echo "✓ SUCCESS: Deployment completed successfully!"
