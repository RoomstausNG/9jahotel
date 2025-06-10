#!/bin/bash

# Wakalor Deployment Script
# Created: June 10, 2025
# Purpose: Deploy updates to production server, run migrations and restart Octane

echo "🚀 Starting deployment process for Wakalor..."

# Stop the Octane server
echo "⏱️ Stopping Octane server..."
php artisan octane:stop

# Git operations - Force update from main branch
echo "📥 Pulling latest code from main branch (force update)..."
git fetch --all
git reset --hard origin/main
git clean -fd

# Install/update dependencies
echo "📦 Updating composer dependencies..."
composer update --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear all caches
echo "🧹 Clearing application caches..."
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Rebuild assets if needed
echo "🏗️ Rebuilding frontend assets with Bun..."
bun install
bun run build

# Cache configuration for better performance
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Update and restart supervisor
echo "🔄 Updating supervisor configuration..."
sudo supervisorctl update

echo "🔄 Reloading supervisor..."
sudo supervisorctl reload

echo "🔄 Restarting all supervisor processes..."
sudo supervisorctl restart all

# Restart Octane
echo "🚀 Starting Octane server..."
php artisan octane:start --watch

echo "✅ Deployment completed successfully!"