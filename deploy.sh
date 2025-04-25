#!/bin/bash

set -e

# Define application and image names
APP_NAME="wakalor"
IMAGE_NAME="9jahotel:latest"

echo "🔧 Building the Docker image locally..."

# Build the Docker image on the local VPS
# docker build -t $IMAGE_NAME .
docker build --no-cache -t $IMAGE_NAME .

echo "🧹 Stopping old services in Swarm..."
# Scale down and remove the old service if it exists
docker service scale ${APP_NAME}=0 || true
docker service rm ${APP_NAME} || true

echo "🚀 Deploying new service in Docker Swarm..."

# Deploy the new service using Docker Stack
docker stack deploy -c docker-compose.yml wakalor

echo "✅ Deployment complete!"
