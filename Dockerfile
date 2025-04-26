FROM oven/bun:latest

WORKDIR /app

# Install Redis CLI and other system dependencies
RUN apt-get update && apt-get install -y \
    redis-tools \
    ca-certificates \
    perl libterm-readline-gnu-perl \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy package.json first for better layer caching
COPY package.json ./

# Install dependencies 
RUN bun install --production

# Copy source code
COPY . .

# Create necessary directories if any needed for volumes
RUN mkdir -p /app/data

# Build the project
RUN bun run build  # Assuming 'bun run prod' is not valid; replace if needed

# Expose the port your app runs on
EXPOSE 3003

# Copy the wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh

# Make sure it's executable
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Add healthcheck for the container itself
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:3003/health || exit 1

# Command to run the application
CMD ["/usr/local/bin/wait-for-redis.sh", "redis:6379", "--", "bun", "run", "start"]