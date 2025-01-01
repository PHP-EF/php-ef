#!/bin/sh
# Start Redis server
redis-server /etc/redis/redis.conf
crond -b -l 0 -L /var/log/cron.log

# Execute the CMD
exec "$@"