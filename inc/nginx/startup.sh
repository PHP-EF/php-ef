# Override Nginx Config
cp /home/site/wwwroot/inc/nginx/default /etc/nginx/sites-enabled/default
service nginx restart

# Cleanup Release.zip
rm /home/site/wwwroot/release.zip

# Install, Configure & Start crontab, Redis & sqlite3
apt-get update -y
apt-get install -y cron redis-server sqlite3
echo "* * * * * /usr/local/bin/php /home/site/wwwroot/inc/scheduler/scheduler.php 1>> /dev/null 2>&1" | crontab -
service cron start

# Overwrite Redis Config File
cp /home/site/wwwroot/inc/nginx/redis.conf /etc/redis/redis.conf
redis-cli shutdown
service redis-server start

# Link Custom PHP INI
ln -s /home/site/wwwroot/inc/nginx/php/php.ini /usr/local/etc/php/conf.d/custom.ini