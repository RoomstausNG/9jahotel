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

# Checkout the dashboard branch
echo ">> Checking out dashboard branch..."
git checkout -B dashboard origin/dashboard
if [ $? -eq 0 ]; then
  echo "✓ SUCCESS: Git checkout dashboard branch"
else
  echo "✗ ERROR: Git checkout dashboard branch failed"
  exit 1
fi

# Fix permissions on problematic files before pulling
echo ">> Adjusting file permissions..."
chmod -f 664 public/.user.ini || true

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
echo ">> Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Fix storage permissions
echo ">> Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Bring the application back online
echo ">> Bringing application online..."
php artisan up

echo "✓ SUCCESS: Deployment completed successfully!"
