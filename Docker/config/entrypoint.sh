#!/bin/sh
# Start Redis server
redis-server /etc/redis/redis.conf

# Execute the CMD
exec "$@"