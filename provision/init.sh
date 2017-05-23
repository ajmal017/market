apt-get install -y apt-transport-https lsb-release ca-certificates

if [ ! -e /etc/apt/sources.list.d/sury.php.list ]; then
	wget -O /etc/apt/trusted.gpg.d/sury.php.gpg https://packages.sury.org/php/apt.gpg
	echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/sury.php.list
fi

apt-get update
apt-get install -y curl git nginx php7.1-cli php7.1-fpm php7.1-zip unzip zip

if [ ! -e /usr/local/bin/composer ]; then
	php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
	EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
	ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', '/tmp/composer-setup.php');")

	if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
		>&2 echo 'ERROR: Invalid composer installer signature'
	else
		php /tmp/composer-setup.php -- --install-dir=/usr/local/bin --filename=composer
	fi
fi

cd /vagrant && sudo -u vagrant composer install

. /vagrant/vendor/sharkodlak/development/init.sh
