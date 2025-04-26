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
RUN bun run prod

# Expose the port your app runs on
EXPOSE 3003

# Add healthcheck for the container itself
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:3003/api/health || exit 1

# Command to run the application
CMD ["bun", "run", "start"]