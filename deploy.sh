#!/bin/bash

set -e

# Define application and image names
APP_NAME="wakalor"
IMAGE_NAME="9jahotel:latest"

echo "🔧 Building the Docker image locally..."

# Build the Docker image on the local VPS
# docker build -t $IMAGE_NAME .
docker build --no-cache -t 9jahotel:latest .

echo "🧹 Stopping old services in Swarm..."
# Scale down and remove the old service if it exists
docker service scale wakalor=0 || true
docker service rm 9jahotel:latest || true

echo "🚀 Deploying new service in Docker Swarm..."

# Deploy the new service using Docker Stack
# docker stack deploy -c docker-compose.yml wakalor
docker stack deploy --with-registry-auth -c docker-compose.yml wakalor

echo "✅ Deployment complete!"
