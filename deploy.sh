#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Configuration ---
PROJECT_DIR="/www/wwwroot/9jahotel_api" 
PM2_APP_NAME="wakalor"                 # The name of your app in PM2
GIT_BRANCH="api_v0"                    # The branch to pull from
ENTRY_POINT="dist/index.js"           # Optional if you're using a Bun script
NUM_INSTANCES=3                       # Specify the number of instances you want to run

# --- Deployment Steps ---
echo "🚀 Starting deployment to $PROJECT_DIR..."

# Navigate to the project directory
cd "$PROJECT_DIR" || { echo "❌ Error: Could not navigate to $PROJECT_DIR"; exit 1; }
echo "📂 Changed directory to $(pwd)"

# Pull the latest changes from the specified branch
echo "🔄 Pulling latest code from branch '$GIT_BRANCH'..."
git checkout "$GIT_BRANCH"
git pull production "$GIT_BRANCH"

# Print the current user (for debug)
echo "👤 Running as user: $(whoami)"

# Install/update dependencies using Bun (production only)
echo "📦 Installing dependencies with Bun..."
bun install --frozen-lockfile --production

# Sync database schema
echo "🧠 Introspecting the database..."
bun run db:pull

# Build the application
echo "🏗️ Building application for production..."
export NODE_ENV=production
bun run build

# Restart the application using PM2 with Bun
echo "🔁 Restarting application '$PM2_APP_NAME' with PM2 + Bun..."
pm2 restart "$PM2_APP_NAME" || pm2 start bun --name "$PM2_APP_NAME" -- run start -i $NUM_INSTANCES --interpreter none --exec_mode cluster

# Optional: Save the PM2 process list
pm2 save

# Done!
echo "✅ Deployment complete at $(date) by $(whoami)"
exit 0