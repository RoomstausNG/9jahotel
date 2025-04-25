#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Configuration ---
# IMPORTANT: Set these variables according to your environment
PROJECT_DIR="/var/www/wakalor-backend" # Directory where your project is cloned on the server
PM2_APP_NAME="wakalor-backend"        # The name of your app in PM2
GIT_BRANCH="main"                     # The branch to pull from
ENTRY_POINT="dist/server.js"          # The entry point for your built application

# --- Deployment Steps ---
echo "Starting deployment to $PROJECT_DIR..."

# Navigate to the project directory
cd "$PROJECT_DIR" || { echo "Error: Could not navigate to $PROJECT_DIR"; exit 1; }
echo "Changed directory to $(pwd)"

# Pull the latest changes from the specified branch
echo "Pulling latest code from branch '$GIT_BRANCH'..."
git checkout "$GIT_BRANCH"
git pull origin "$GIT_BRANCH"

# Install/update dependencies using Bun (production only)
echo "Installing dependencies..."
bun install --frozen-lockfile --production

# Build the application on the server
echo "Building application..."
# If you need environment variables during build, uncomment and configure:
# export NODE_ENV=production
# export SOME_BUILD_VAR=value
bun run build

# Restart the application using PM2
echo "Restarting application '$PM2_APP_NAME' with PM2..."
# The `||` ensures that if the app isn't running, it tries to start it.
pm2 restart "$PM2_APP_NAME" || pm2 start "$ENTRY_POINT" --name "$PM2_APP_NAME"

# Optional: Save the PM2 process list if a new process was started
pm2 save

echo "Deployment complete!"

exit 0