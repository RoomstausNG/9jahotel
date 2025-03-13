#!/bin/bash
set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

# Print with colors
echo_success() {
    echo -e "${GREEN}$1${NC}"
}
echo_error() {
    echo -e "${RED}$1${NC}"
}
echo_warning() {
  echo -e "${YELLOW}$1${NC}"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if running in Swarm mode
if ! docker info | grep -q "Swarm: active"; then
  echo_warning "Docker is not in Swarm mode. Initializing Swarm..."
    docker swarm init --advertise-addr $(hostname -I | awk '{print $1}') || {
        echo_error "Failed to initialize Docker Swarm."
        exit 1
    }
    echo_success "Docker Swarm initialized successfully."
fi

# Check if .env file exists, create if not
if [ ! -f .env ]; then
    echo "Creating .env file with default values..."
    cat > .env << EOF
DB_DATABASE=wakalordb
DB_USERNAME=wakalor
DB_PASSWORD=wakalorpassword
MYSQL_ROOT_PASSWORD=rootpassword
EOF
    echo_success ".env file created successfully."
else
    echo_success ".env file already exists."
fi

# Load environment variables
export $(grep -v '^#' .env | xargs)

# Build the Docker image
echo "Building Docker image..."
docker build -t wakalor . || {
  echo_error "Failed to build Docker image."
    exit 1
}
echo_success "Docker image built successfully."

# Deploy the stack
echo "Deploying stack to Swarm..."
docker stack deploy -c docker-compose.yml wakalor || {
  echo_error "Failed to deploy stack."
  exit 1
}
echo_success "Stack deployed successfully."

# Show services status
echo "Checking service status..."
docker service ls --filter name=wakalor
echo_success "Deployment completed successfully!"

# Show access instructions
echo ""
echo "-------------------------------------------------------------"
echo "Your application is now deployed."
echo "To view logs, run: docker service logs wakalor_wakalor"
echo "To scale a service, run: docker service scale wakalor_wakalor=N"
echo "To update, simply run this script again."
echo "To stop all services, run: docker stack rm wakalor"
echo "-------------------------------------------------------------"
