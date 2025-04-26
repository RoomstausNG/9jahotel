#!/bin/bash

echo "Waiting for Redis to be ready..."
while ! redis-cli -h redis ping; do
  echo "Redis is not ready yet, retrying..."
  sleep 5
done

echo "Redis is ready. Starting the app..."
exec "$@"