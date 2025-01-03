#!/bin/sh
# Start Redis server
redis-server /etc/redis/redis.conf
/usr/local/bin/supercronic /supercronic/crontab &

# Execute the CMD
exec "$@"