#!/bin/bash

set -e  # exit immediately if any command fails

echo "📦 Pulling latest changes from git..."
git reset --hard HEAD
git pull production api_v1

echo "🐳 Pulling latest Docker base images..."
docker compose pull

echo "🔨 Building Docker containers without cache..."
docker compose build --no-cache  # Ensures no caching during build

echo "🚀 Stopping and removing old containers (if any)..."
docker compose down  # Stops and removes any running containers

echo "🚀 Starting Docker containers..."
docker compose up -d 

echo "📋 Showing Docker container status..."
docker ps

echo "✅ Deployment complete."