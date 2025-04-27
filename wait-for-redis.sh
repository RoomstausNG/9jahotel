#!/bin/sh

# Wait for Redis to become available
echo "Waiting for Redis to be ready..."

until redis-cli -h redis ping | grep PONG > /dev/null 2>&1; do
  echo "Redis not ready yet... sleeping..."
  sleep 3
done

echo "✅ Redis is ready!, App is starting..."

# wait-for-it.sh style
until mysqladmin ping -h"$DB_HOST" --silent; do
  echo "Waiting for MySQL at $DB_HOST..."
  sleep 2
done

echo "MySQL is up — starting app"

# Replace shell with bun app (important: keep PID 1)
exec "$@"
