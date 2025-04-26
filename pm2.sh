#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Configuration ---
PROJECT_DIR="/www/wwwroot/9jahotel_api"
PM2_APP_NAME="wakalor"                 # The name of your app in PM2
GIT_BRANCH="api_v1"                    # The branch to pull from
ENTRY_POINT="dist/index.js"             # Entry point for your application
NUM_INSTANCES=4                        # Specify the number of instances you want to run

# --- Deployment Steps ---
echo "🚀 Starting zero-downtime deployment to $PROJECT_DIR..."

# Navigate to the project directory
cd "$PROJECT_DIR" || { echo "❌ Error: Could not navigate to $PROJECT_DIR"; exit 1; }
echo "📂 Changed directory to $(pwd)"

# Create ecosystem file if it doesn't exist
if [ ! -f "ecosystem.config.js" ]; then
  echo "📝 Creating PM2 ecosystem file..."
  cat > ecosystem.config.js << EOF
module.exports = {
  apps: [{
    name: "${PM2_APP_NAME}",
    script: "bun",
    args: "${ENTRY_POINT}",
    instances: ${NUM_INSTANCES},
    exec_mode: "cluster",
    interpreter: "bun",
    watch: false,
    max_memory_restart: "6G",
    env: {
      NODE_ENV: "production"
    },
    wait_ready: true,
    listen_timeout: 3000,
    kill_timeout: 5000
  }]
};
EOF
fi

# Pull the latest changes from the specified branch
echo "🔄 Pulling latest code from branch '$GIT_BRANCH'..."
git checkout "$GIT_BRANCH"
git pull production "$GIT_BRANCH"

# Print the current user (for debug)
echo "👤 Running as user: $(whoami)"

# Make bun available
export BUN_INSTALL="/home/www/.bun"
export PATH="$BUN_INSTALL/bin:$PATH"

# Install/update dependencies using bun (production only)
echo "📦 Installing dependencies with Bun..."
bun install --production

# Sync database schema
echo "🧠 Introspecting the database..."
bun run db:pull

# Build the application
echo "🏗️ Building application for production..."
export NODE_ENV=production
bun run build

# Check if the app is already running in PM2
if pm2 list | grep -q "$PM2_APP_NAME"; then
  echo "🔄 Performing zero-downtime reload of '$PM2_APP_NAME'..."
  pm2 reload ecosystem.config.js --update-env
else
  echo "🚀 Starting application '$PM2_APP_NAME' for the first time..."
  pm2 start ecosystem.config.js
fi


echo "🔒 Setting up PM2 startup if needed..."
pm2 startup systemd -u $(whoami) --hp $(eval echo ~$USER) || true


# Save the PM2 process list
echo "💾 Saving PM2 process list..."
pm2 save

# Monitor deployment status
echo "🔍 Checking application status..."

# Health check after reload
APP_STATUS=$(pm2 info "$PM2_APP_NAME" | grep "status" | awk '{print $4}')

if [ "$APP_STATUS" = "online" ]; then
  echo "✅ Application '$PM2_APP_NAME' is running successfully!"
else
  echo "❌ Application '$PM2_APP_NAME' is NOT running correctly. Please check logs."
  pm2 logs "$PM2_APP_NAME"
  exit 1
fi

# Done!
echo "🏁 Zero-downtime deployment complete at $(date) by $(whoami)"
exit 0