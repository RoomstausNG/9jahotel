#!/bin/bash
set -e

echo ">> Deploying application..."

# Store the current state
echo ">> Saving current state..."
git stash

# Fetch the latest changes
echo ">> Fetching latest changes..."
git fetch
if [ $? -eq 0 ]; then
  echo "✓ SUCCESS: Git fetch"
else
  echo "✗ ERROR: Git fetch failed"
  exit 1
fi

# Pull the latest changes with a strategy to handle permission issues
echo ">> Force pulling latest changes..."
git pull --rebase --strategy-option=theirs
if [ $? -eq 0 ]; then
  echo "✓ SUCCESS: Git pull from dashboard branch"
else
  echo "✗ ERROR: Git pull from dashboard branch failed"
  # Try alternative approach if standard pull fails
  git reset --hard origin/dashboard
  if [ $? -eq 0 ]; then
    echo "✓ SUCCESS: Git reset to dashboard branch"
  else
    echo "✗ ERROR: Git force pull from dashboard branch failed"
    echo "Stopping script execution"
    exit 1
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
echo ">> Setting permissions to 777..."
chmod -R 777 storage bootstrap/cache

# Ensure cache directories exist with proper permissions
echo ">> Ensuring cache directories exist..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
chmod -R 777 storage/framework

# Set proper ownership for web server
echo ">> Setting proper ownership..."
chown -R www-data:www-data storage bootstrap/cache

# Bring the application back online
echo ">> Bringing application online..."
php artisan up

echo "✓ SUCCESS: Deployment completed successfully!"
