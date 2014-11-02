#!/usr/bin/env bash
echo "Setting up version " $1
echo "You still need to use Versions to send to WP.org"

function deploy () {
	rm -rf ~/Sites/timber/vendor
	cd ..
	composer install --no-dev
	rm -rf ~/Sites/timber/vendor/dannyvankooten/php-router/.git
	cd ~/Sites/timber-wp
	mkdir tags/$1
	
	cp -r ~/Sites/timber/functions tags/$1/functions
	cp -r ~/Sites/timber/timber-starter-theme tags/$1/timber-starter-theme
	cp -r ~/Sites/timber/vendor tags/$1/vendor
	cp ~/Sites/timber/LICENSE.txt tags/$1/LICENSE.txt
	cp ~/Sites/timber/README.md tags/$1/README.md
	cp ~/Sites/timber/readme.txt tags/$1/readme.txt
	cp ~/Sites/timber/timber.php tags/$1/timber.php
	svn add tags/$1
	cd tags/$1
	svn commit -m "updating to $1"
	cd ~/Sites/timber-wp/trunk
	svn commit -m "updating to $1"
}

deploy $1