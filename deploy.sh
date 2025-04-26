#!/bin/bash

set -e

# Define variables
APP_NAME="wakalor"
IMAGE_NAME="9jahotel:latest"
LOCAL_REGISTRY="localhost:5000"
REGISTRY_IMAGE="$LOCAL_REGISTRY/9jahotel:latest"

echo "📥 Resetting and pulling latest code from Git..."
git reset --hard HEAD
RETRIES=3
until git pull production api_v1 || [ $RETRIES -eq 0 ]; do
  RETRIES=$((RETRIES-1))
  echo "❌ Git pull failed. Retrying... ($RETRIES attempts left)"
  sleep 5
done
if [ $RETRIES -eq 0 ]; then
  echo "❌ Git pull failed after multiple attempts. Exiting."
  exit 1
fi

echo "🔧 Building the Docker image locally..."
docker build --no-cache -t $IMAGE_NAME .

echo "📤 Tagging and pushing image to local registry ($LOCAL_REGISTRY)..."
docker tag $IMAGE_NAME $REGISTRY_IMAGE
docker push $REGISTRY_IMAGE

# Verify registry access
echo "🔍 Verifying registry access..."
if ! curl -s http://$LOCAL_REGISTRY/v2/ > /dev/null; then
  echo "❌ Cannot access registry at $LOCAL_REGISTRY. Is it running?"
  exit 1
fi

echo "🚀 Deploying new service in Docker Swarm..."
docker stack deploy -c docker-compose.yml $APP_NAME

echo "⏳ Waiting for services to start..."
sleep 30

echo "📊 Checking service status..."
docker service ls --filter "name=${APP_NAME}_"

echo "🧼 Cleaning up unused Docker resources..."
docker system prune -a -f

echo "checking memory"
docker system df

echo "📦 Active containers for '$APP_NAME':"
docker ps --filter "name=${APP_NAME}_" --format "table {{.Names}}\t{{.Status}}\t{{.Image}}"

# Health check for application
echo "🏥 Checking application health..."
ATTEMPTS=0
MAX_ATTEMPTS=7
until $(curl --output /dev/null --silent --fail http://0.0.0.0:3003/health) || [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; do
  ATTEMPTS=$((ATTEMPTS+1))
  echo "  Health check attempt $ATTEMPTS of $MAX_ATTEMPTS..."
  sleep 8
done

if [ $ATTEMPTS -eq $MAX_ATTEMPTS ]; then
  echo "❌ Application failed to become healthy after deployment"
  echo "📋 Logs from app service:"
  docker service logs ${APP_NAME}_app --tail 50 > deployment_logs.txt
  exit 1
else
  echo "✅ Application is healthy!"
fi

echo "✅ Deployment complete!"
