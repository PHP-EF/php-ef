#!/bin/sh
# Start Redis server
redis-server /etc/redis/redis.conf
crond -b

# Execute the CMD
exec "$@"