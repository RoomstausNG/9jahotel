#!/bin/bash

set -e

# Define variables
APP_NAME="wakalor"
IMAGE_NAME="9jahotel:latest"
LOCAL_REGISTRY="localhost:5000"
REGISTRY_IMAGE="$LOCAL_REGISTRY/9jahotel:latest"
NETWORK_NAME="9jahotel"

# Check if the network exists
# if ! docker network ls --filter name=$NETWORK_NAME --format "{{.Name}}" | grep -w $NETWORK_NAME > /dev/null; then
#   echo "Network '$NETWORK_NAME' not found, creating it..."
#   docker network create --driver overlay $NETWORK_NAME
# else
#   echo "Network '$NETWORK_NAME' already exists, skipping creation."
# fi

echo "📥 Resetting and pulling latest code from Git..."
git reset --hard HEAD
git pull production api_v1

echo "🔧 Building the Docker image locally..."
docker build --no-cache -t $IMAGE_NAME .

echo "📤 Tagging and pushing image to local registry ($LOCAL_REGISTRY)..."
docker tag $IMAGE_NAME $REGISTRY_IMAGE
docker push $REGISTRY_IMAGE

# Add a check that the registry is accessible
echo "🔍 Verifying registry access..."
if ! curl -s http://$LOCAL_REGISTRY/v2/ > /dev/null; then
  echo "❌ Cannot access registry at $LOCAL_REGISTRY. Is it running?"
  exit 1
fi

echo "🧹 Removing old stack deployment..."
docker stack rm $APP_NAME || true

# Wait for stack to be fully removed
echo "⏳ Waiting for old stack to be removed..."
while docker stack ps $APP_NAME 2>/dev/null | grep -q "$APP_NAME"; do
  echo "  Still removing old stack..."
  sleep 10
done

echo "🚀 Deploying new service in Docker Swarm..."
docker stack deploy -c docker-compose.yml $APP_NAME

echo "⏳ Waiting for services to start..."
sleep 5

echo "📊 Checking service status..."
docker service ls --filter "name=${APP_NAME}_"

echo "🧼 Cleaning up unused Docker resources..."
docker system prune -a --volumes

echo "checking memory"
docker system df

echo "📦 Active containers for '$APP_NAME':"
docker ps --filter "name=${APP_NAME}_" --format "table {{.Names}}\t{{.Status}}\t{{.Image}}"

# Add health check for application
echo "🏥 Checking application health..."
ATTEMPTS=0
MAX_ATTEMPTS=10
until $(curl --output /dev/null --silent --fail http://localhost:3003/api/health) || [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; do
  ATTEMPTS=$((ATTEMPTS+1))
  echo "  Health check attempt $ATTEMPTS of $MAX_ATTEMPTS..."
  sleep 3
done

if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
  echo "❌ Application failed to become healthy after deployment"
  echo "📋 Logs from app service:"
  docker service logs ${APP_NAME}_app --tail 50
  exit 1
else
  echo "✅ Application is healthy!"
fi

echo "✅ Deployment complete!"