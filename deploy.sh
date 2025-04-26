#!/bin/bash

set -e  # exit immediately if any command fails

echo "📦 Pulling latest changes from git..."
git reset --hard HEAD
git pull production api_v1

echo "🐳 Pulling latest Docker base images..."
docker compose pull

echo "🔨 Building Docker containers..."
docker compose build

echo "🚀 Starting Docker containers..."
docker compose up -d

echo "📋 Showing Docker container status..."
docker ps

echo "✅ Deployment complete."