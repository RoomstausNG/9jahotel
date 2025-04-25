#!/bin/bash

set -e

APP_NAME="bun-app"
IMAGE_NAME="yourusername/yourapp:latest"

echo "📦 Pulling latest image..."
docker pull $IMAGE_NAME

echo "🧹 Stopping old services in Swarm..."
docker service scale ${APP_NAME}=0 || true
docker service rm ${APP_NAME} || true

echo "🚀 Deploying new service in Docker Swarm..."
docker stack deploy -c docker-compose.yml myapp

echo "✅ Deployment complete!"
