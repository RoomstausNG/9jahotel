# Use specific Bun version
FROM oven/bun:1.2.10 as base

WORKDIR /app

# Install redis-cli and curl once
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy only package files first (cache optimization)
COPY package.json bun.lock ./

# Install only production dependencies
RUN bun install --frozen-lockfile --production

# Copy app source code
COPY . .

# Build the app (with minify)
RUN if grep -q '"build"' package.json; then bun run build --minify; fi

# Install wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Expose application port
EXPOSE 3003

# Healthcheck (optional)
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1

# Wait for Redis and Start app
ENTRYPOINT ["wait-for-redis.sh"]
CMD ["bun", "run", "dist/index.js"]