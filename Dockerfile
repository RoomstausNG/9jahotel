# Stage 1: Base Image
FROM oven/bun:1.2.10 AS base
WORKDIR /app

# Install redis-cli if needed
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Stage 2: Install Production Dependencies
FROM base AS deps
COPY package.json bun.lock ./
RUN bun install --production

# Stage 3: Build the App
FROM base AS build
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN if grep -q '"build"' package.json; then bun run build; fi

# Stage 4: Release Image
FROM base AS release
WORKDIR /app

# Copy production node_modules
COPY --from=deps /app/node_modules ./node_modules

# Copy built app
COPY --from=build /app/dist ./dist
COPY --from=build /app/package.json ./package.json

# Copy wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Expose application port
EXPOSE 3003

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1

# Entry point (wait for redis first)
ENTRYPOINT ["wait-for-redis.sh"]

# Command to start app
CMD ["bun", "run", "dist/index.js"]