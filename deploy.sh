#!/bin/bash

set -e  # exit immediately if any command fails

# --- Git Operations ---
echo "📦 Pulling latest changes from git..."
git reset --hard HEAD
git pull production event
# Get the short commit hash for tagging
GIT_HASH=$(git rev-parse --short HEAD)
echo "🏷️ Using Git Hash for tag: $GIT_HASH"
echo

# --- Docker Build ---
echo "🐳 Pulling latest Docker base images..."
docker compose pull

echo "🔨 Building Docker containers without cache..."
docker compose build --no-cache  # Ensures no caching during build

# --- Tagging and Pushing to Local Registry ---
PROJECT_NAME=$(basename "$PWD" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]*//g') # Get sanitized project name
SOURCE_IMAGE_NAME="${PROJECT_NAME}-app:latest" # Compose usually tags with :latest by default after build
TARGET_IMAGE_NAME_HASH="localhost:5000/${PROJECT_NAME}-app:${GIT_HASH}"
TARGET_IMAGE_NAME_LATEST="localhost:5000/${PROJECT_NAME}-app:latest"

echo "🏷️ Tagging image ${SOURCE_IMAGE_NAME} as ${TARGET_IMAGE_NAME_HASH} and ${TARGET_IMAGE_NAME_LATEST}..."
# Ensure the source image exists before tagging
if docker image inspect "${SOURCE_IMAGE_NAME}" > /dev/null 2>&1; then
    docker tag "${SOURCE_IMAGE_NAME}" "${TARGET_IMAGE_NAME_HASH}"
    docker tag "${SOURCE_IMAGE_NAME}" "${TARGET_IMAGE_NAME_LATEST}" # Also tag as latest for the registry
else
    echo "⚠️ Error: Source image ${SOURCE_IMAGE_NAME} not found after build. Cannot tag."
    exit 1
fi

echo " Pushing image ${TARGET_IMAGE_NAME_HASH} to local registry..."
docker push "${TARGET_IMAGE_NAME_HASH}"
echo " Pushing image ${TARGET_IMAGE_NAME_LATEST} to local registry..."
docker push "${TARGET_IMAGE_NAME_LATEST}"
echo

# --- Deployment ---
echo "🚀 Stopping and removing old containers (if any)..."
docker compose down  # Stops and removes any running containers


echo "🚀 Starting Docker containers..."
# Important: Ensure your docker-compose.yml file references the image
# from the local registry, e.g., image: localhost:5000/yourproject-app:latest
docker compose up -d

echo "📋 Showing Docker container status..."
docker ps

echo "✅ Deployment complete."