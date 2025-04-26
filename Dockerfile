# # Use official Bun image
# FROM oven/bun:1 as base
# WORKDIR /app

# # Install redis-cli and curl
# RUN apt-get update && apt-get install -y redis-tools curl \
#     && apt-get clean && rm -rf /var/lib/apt/lists/*

# # Copy package files
# COPY package.json bun.lock ./

# # Install production dependencies (no frozen-lockfile)
# RUN bun install --production

# # Copy app source
# COPY . .

# # Build the app (only if you have build script)
# RUN if grep -q '"build"' package.json; then bun run build; fi

# # Copy wait-for-redis script
# COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
# RUN chmod +x /usr/local/bin/wait-for-redis.sh

# # Expose the port
# EXPOSE 3003

# # Healthcheck (optional)
# HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
#     CMD curl -f http://127.0.0.1:3003/health || exit 1

# # Wait for redis first, then run app
# ENTRYPOINT ["/usr/local/bin/wait-for-redis.sh"]
# CMD ["bun", "dist/index.js"]

# Use official Bun image
FROM oven/bun:1 as base
WORKDIR /app

# Install redis-cli and curl
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy package files
COPY bun.lock ./

# Install production dependencies (no frozen-lockfile)
RUN bun install --production

# Copy app source
COPY . .

# Build the app (only if you have build script)
RUN if grep -q '"build"' package.json; then bun run build; fi

# Copy wait-for-redis script
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh

# Expose the port
EXPOSE 3003

# Healthcheck (optional)
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1

# Wait for redis first, then run app
ENTRYPOINT ["/usr/local/bin/wait-for-redis.sh"]
CMD ["bun", "dist/index.js"]