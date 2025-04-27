#!/bin/sh

# Wait for Redis to become available
echo "Waiting for Redis to be ready..."

until redis-cli -h redis ping | grep PONG > /dev/null 2>&1; do
  echo "Redis not ready yet... sleeping..."
  sleep 3
done

echo "✅ Redis is ready!, MySQL is starting..."
exec bun run db:pull

echo "MySQL is up — starting app"

# Replace shell with bun app (important: keep PID 1)
exec "$@"
