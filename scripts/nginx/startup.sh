# Override Nginx Config
cp /home/site/wwwroot/scripts/nginx/default /etc/nginx/sites-enabled/default
service nginx restart

# Cleanup Release.zip
rm /home/site/wwwroot/release.zip

# Install, Configure & Start crontab
apt-get update -y
apt-get install -y cron
echo "* * * * * /usr/local/bin/php /git/ib-sa-report/scripts/scheduler/scheduler.php 1>> /dev/null 2>&1" | crontab -
service cron start