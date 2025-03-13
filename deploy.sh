#!/bin/bash

# Define colors for better readability
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Print header
echo -e "${BLUE}==================================================${NC}"
echo -e "${GREEN}Starting Laravel Dashboard Update and Optimization${NC}"
echo -e "${BLUE}==================================================${NC}"

# Function to check command execution status
check_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ SUCCESS: $1${NC}"
    else
        echo -e "${RED}✗ ERROR: $1 failed${NC}"
        echo -e "${RED}Stopping script execution${NC}"
        exit 1
    fi
}

# Set correct permissions
sudo chmod -R 755 storage bootstrap/cache
sudo chmod -R 777 storage/framework storage/logs bootstrap/cache

# Navigate to project directory - uncomment and modify if needed
# cd /path/to/your/laravel/project
# check_status "Changed directory to project folder"

echo -e "\n${BLUE}>> Fetching latest changes...${NC}"
git fetch origin
check_status "Git fetch"

echo -e "\n${BLUE}>> Checking out dashboard branch...${NC}"
git checkout dashboard
check_status "Git checkout dashboard branch"

echo -e "\n${BLUE}>> Force pulling latest changes...${NC}"
git reset --hard origin/dashboard
check_status "Git force pull from dashboard branch"

echo -e "\n${BLUE}>> Installing/updating Composer dependencies...${NC}"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
check_status "Composer dependencies installation"

echo -e "\n${BLUE}>> Clearing and rebuilding cache...${NC}"
php artisan optimize:clear
check_status "Cache clearing"

echo -e "\n${BLUE}>> Running database migrations...${NC}"
php artisan migrate --force
check_status "Database migrations"

echo -e "\n${BLUE}>> Optimizing Laravel...${NC}"
php artisan optimize
check_status "Laravel optimization"

echo -e "\n${BLUE}>> Caching routes...${NC}"
php artisan route:cache
check_status "Route caching"

echo -e "\n${BLUE}>> Caching configurations...${NC}"
php artisan config:cache
check_status "Config caching"

echo -e "\n${BLUE}>> Caching views...${NC}"
php artisan view:cache
check_status "View caching"


# If using npm/yarn for frontend, uncomment these lines
# echo -e "\n${BLUE}>> Installing NPM dependencies...${NC}"
# npm ci
# check_status "NPM dependencies installation"

# echo -e "\n${BLUE}>> Building frontend assets...${NC}"
# npm run build
# check_status "Frontend assets build"

echo -e "\n${GREEN}==========================================${NC}"
echo -e "${GREEN}✓ Update and optimization completed successfully!${NC}"
echo -e "${GREEN}==========================================${NC}"

exit 0
