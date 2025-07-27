#!/bin/bash

# 9jahotel Deployment Script
# Created: June 10, 2025
# Purpose: Deploy updates to production server, run migrations and restart Octane

echo "🚀 Starting deployment process for 9jahotel..."

# Stop the Octane server
echo "⏱️ Stopping Octane server..."
php artisan octane:stop 2>/dev/null || echo "Octane was not running"

# Fix permissions on public/.user.ini to avoid git errors
echo "🔧 Fixing file permissions..."
if [ -f public/.user.ini ]; then
  sudo chown $(whoami):$(whoami) public/.user.ini 2>/dev/null || true
  chmod 644 public/.user.ini 2>/dev/null || true
fi

# Check if git remote exists and fix if needed
echo "🔍 Checking git configuration..."
if ! git remote get-url production >/dev/null 2>&1; then
    echo "⚠️ Git remote 'production' not found. Please configure it:"
    echo "Run: git remote add production <your-repository-url>"
    echo "Or: git remote set-url production <your-repository-url>"
    exit 1
fi

# Git operations - Force update from main branch
echo "📥 Pulling latest code from main branch..."

# Stash any local changes
echo "💾 Stashing local changes..."
git stash push -m "Pre-deployment stash $(date)" --include-untracked || true

# Force fetch and reset
echo "🔄 Fetching latest changes..."
git fetch production main || {
    echo "❌ Failed to fetch from production. Please check your git remote configuration."
    exit 1
}

echo "🔄 Resetting to latest main branch..."
git reset --hard production/main || {
    echo "❌ Failed to reset to production/main. Trying alternative approach..."
    git pull production main --force || {
        echo "❌ Failed to pull changes. Please resolve manually."
        exit 1
    }
}

# Clean untracked files
echo "🧹 Cleaning untracked files..."
git clean -fd

# Install/update dependencies
echo "📦 Updating composer dependencies..."
composer update --no-interaction --prefer-dist --optimize-autoloader --no-dev || {
    echo "❌ Composer update failed"
    exit 1
}

# Set proper directory permissions
echo "🔒 Setting directory permissions..."
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Clear all caches
echo "🧹 Clearing application caches..."
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations (uncomment when ready)
echo "🔄 Running database migrations..."
read -p "Do you want to run migrations? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force || {
        echo "⚠️ Migration failed, but continuing deployment..."
    }
else
    echo "⏭️ Skipping migrations"
fi

# Check if Node.js/npm is available
if command -v npm &> /dev/null; then
    echo "🏗️ Rebuilding frontend assets..."
    npm install || {
        echo "⚠️ npm install failed, trying with --force..."
        npm install --force || echo "❌ npm install failed completely"
    }
    npm run build || {
        echo "⚠️ Asset build failed, but continuing..."
    }
else
    echo "⚠️ npm not found, skipping asset compilation"
fi

# Cache configuration for better performance
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Update and restart supervisor if available
if command -v supervisorctl &> /dev/null; then
    echo "🔄 Managing supervisor processes..."

    # Check if supervisor is running
    if sudo supervisorctl status >/dev/null 2>&1; then
        echo "🔄 Updating supervisor configuration..."
        sudo supervisorctl update || echo "⚠️ Failed to update supervisor"

        echo "🔄 Reloading supervisor..."
        sudo supervisorctl reload || echo "⚠️ Failed to reload supervisor"

        echo "🔄 Restarting Laravel worker processes..."
        sudo supervisorctl restart laravel-worker:* 2>/dev/null || echo "⚠️ No Laravel workers found"
    else
        echo "⚠️ Supervisor daemon not running"
    fi
else
    echo "⚠️ Supervisorctl not found, skipping supervisor commands"
fi

# Final status check
echo "🏁 Deployment Summary:"
echo "   - Git: $(git rev-parse --short HEAD) on $(git branch --show-current)"
echo "   - PHP: $(php -r 'echo PHP_VERSION;')"
echo "   - Laravel: $(php artisan --version | cut -d' ' -f3)"

echo "✅ Deployment completed!"
echo "🌐 Your application should be available shortly."

# Optionally display application URL
if [ -f .env ]; then
    APP_URL=$(grep "^APP_URL=" .env | cut -d'=' -f2 | tr -d '"')
    if [ ! -z "$APP_URL" ]; then
        echo "🔗 Visit: $APP_URL"
    fi
fi
