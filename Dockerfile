# Use specific Bun version
FROM oven/bun:1.2.10 AS base

WORKDIR /app

# Install redis-cli and curl
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy only package files first
COPY package.json bun.lock ./

# Install only production dependencies (no --frozen-lockfile)
RUN bun install --production

# Copy app source code
COPY . .

# Build the app (with minify)
RUN if grep -q '"build"' package.json; then bun run build --minify; fi

# Copy wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Expose app port
EXPOSE 3003

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1

# EntryPoint and start
ENTRYPOINT ["wait-for-redis.sh"]
CMD ["bun", "run", "dist/index.js"]