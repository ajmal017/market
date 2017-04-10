echo "deb http://packages.dotdeb.org jessie all" > /etc/apt/sources.list.d/dotbeb.list
apt-key adv --keyserver keys.gnupg.net --recv-keys 89DF5277
apt-get update
apt-get install curl nginx php7.1-fpm
mkdir --mode=774 /var/log/www
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
cd /vagrant && composer install
