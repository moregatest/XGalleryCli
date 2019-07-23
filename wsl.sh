sudo apt update
sudo apt -y upgrade
sudo /etc/init.d/mysql restart
sudo /etc/init.d/apache2 restart
sudo /etc/init.d/memcached restart
sudo /etc/init.d/redis-server restart
sudo /etc/init.d/webmin restart

sudo cron start
