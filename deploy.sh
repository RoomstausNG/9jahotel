# #!/bin/bash

# set -e

# # Define application and image names
# APP_NAME="wakalor"
# IMAGE_NAME="9jahotel:latest"

# echo "📥 Resetting and pulling latest code from Git..."
# git reset --hard HEAD
# git pull production api_v1

# echo "🔧 Building the Docker image locally..."
# docker build --no-cache -t $IMAGE_NAME .

# echo "🧹 Stopping old services in Swarm..."
# docker service scale $APP_NAME=0 || true
# docker service rm $APP_NAME || true

# echo "🚀 Deploying new service in Docker Swarm..."
# docker stack deploy --with-registry-auth -c docker-compose.yml $APP_NAME

# echo "🧼 Cleaning up unused containers and images..."
# docker container prune -f
# docker image prune -f

# echo "📦 Currently running containers:"
# docker ps

# echo "✅ Deployment complete!"

#!/bin/bash

set -e

# Define variables
APP_NAME="wakalor"
IMAGE_NAME="9jahotel:latest"
LOCAL_REGISTRY="localhost:5000"
REGISTRY_IMAGE="$LOCAL_REGISTRY/9jahotel:latest"

echo "📥 Resetting and pulling latest code from Git..."
git reset --hard HEAD
git pull production api_v1  # Change branch name if needed

echo "🔧 Building the Docker image locally..."
docker build --no-cache -t 9jahotel:latest .

echo "📤 Tagging and pushing image to local registry ($LOCAL_REGISTRY)..."
docker tag $IMAGE_NAME $REGISTRY_IMAGE
docker push $REGISTRY_IMAGE

echo "🧹 Stopping old services in Swarm..."
# docker service scale $APP_NAME=0 || true
docker service rm $APP_NAME || true

echo "🚀 Deploying new service in Docker Swarm..."
docker stack deploy -c docker-compose.yml $APP_NAME

echo "🧼 Cleaning up unused Docker containers and images..."
docker container prune -f
docker image prune -f

echo "📦 Active containers for '$APP_NAME':"
docker ps --filter "name=${APP_NAME}_" --format "table {{.Names}}\t{{.Status}}\t{{.Image}}"

echo "✅ Deployment complete!"

