FROM oven/bun:1 AS builder
WORKDIR /app
COPY package.json bun.lock ./
RUN bun install --minify
COPY . .
ENV NODE_ENV=production
RUN bun run build

# Runtime stage
FROM oven/bun:1 AS runtime
LABEL name="wakalor-backend"
LABEL version="1.0.0"
WORKDIR /app
RUN apt-get update && apt-get install -y redis-tools curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
COPY wait-for-redis.sh /usr/local/bin/wait-for-redis.sh
RUN chmod +x /usr/local/bin/wait-for-redis.sh
EXPOSE 3003
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://127.0.0.1:3003/health || exit 1
ENTRYPOINT ["/usr/local/bin/wait-for-redis.sh"]
CMD ["bun", "dist/index.js"]