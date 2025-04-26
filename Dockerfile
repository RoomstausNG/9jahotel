# Use official Bun image
FROM oven/bun:latest

WORKDIR /app

# Install redis-cli only if you need it (optional)
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy package files
COPY package.json bun.lockb ./

# Install production dependencies
RUN bun install --production

# Copy app source code
COPY . .

# Create necessary folders if your app needs it
RUN mkdir -p /app/data

# Build app (only if you have "build" script)
RUN if grep -q '"build"' package.json; then bun run build; fi

# Copy wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Expose port
EXPOSE 3003

# Healthcheck (optional)
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1

# Startup
ENTRYPOINT ["wait-for-redis.sh"]
CMD ["bun", "run", "dist/index.js"]