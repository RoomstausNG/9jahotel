#!/bin/bash

set -e

# Define application and image names
APP_NAME="wakalor"
IMAGE_NAME="9jahotel:latest"

echo "📥 Resetting and pulling latest code from Git..."
git reset --hard HEAD
git pull production api_v1  # replace 'main' with your branch if different

echo "🔧 Building the Docker image locally..."
docker build --no-cache -t $IMAGE_NAME .

echo "🧹 Stopping old services in Swarm..."
docker service scale $APP_NAME=0 || true
docker service rm $APP_NAME || true

echo "🚀 Deploying new service in Docker Swarm..."
docker stack deploy --with-registry-auth -c docker-compose.yml $APP_NAME

echo "✅ Deployment complete!"
