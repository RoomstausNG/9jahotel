# Stage 1: Base Image
FROM oven/bun:1.2.10 AS base
WORKDIR /app

# Install redis-cli (only once in base image)
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Stage 2: Install Dependencies (Dev)
FROM base AS deps
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

# Stage 3: Install Dependencies (Prod Only)
FROM base AS prod-deps
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile --production

# Stage 4: Build App
FROM base AS builder
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN if grep -q '"build"' package.json; then bun build --minify; fi

# Stage 5: Final Minimal Image
FROM base AS release
WORKDIR /app

# Copy production deps only
COPY --from=prod-deps /app/node_modules ./node_modules

# Copy built source code
COPY --from=builder /app/. .

# Copy wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Expose the app port
EXPOSE 3003

# Health check (optional but good)
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1

# EntryPoint (wait for Redis first)
ENTRYPOINT ["wait-for-redis.sh"]

# Command to run app
CMD ["bun", "run", "dist/index.js"]