FROM oven/bun:latest

WORKDIR /app

# Install Redis CLI for health checks
RUN apt-get update && apt-get install -y redis-tools && apt-get clean

# Copy package.json first for better layer caching
COPY package.json ./

# Install dependencies
RUN bun install --production

# Copy source code
COPY . .

# Build the project
RUN bun run build --sourcemap --minify --bundle

# Expose the port your app runs on
EXPOSE 3003

# Command to run the application
CMD ["bun", "run", "start"]