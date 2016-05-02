#!/usr/bin/env bash
echo "Setting up version " $1
echo "You still need to use Versions to send to WP.org"

function deploy () {
	cd ~/Sites/timber
	git checkout master
	rm ~/Sites/timber/timber.php
	rm -rf ~/Sites/timber/vendor
	rm -rf ~/Sites/timber/wp-content
	rm -rf ~/Sites/timber/timber-starter-theme
	git clone git@github.com:Upstatement/timber-starter-theme.git
	rm -rf ~/Sites/timber/timber-starter-theme/.git
	composer install --no-dev
	rm -rf ~/Sites/timber/vendor/upstatement/routes/.git
	cd ~/Sites/timber-wp
	mkdir tags/$1

	cp -r ~/Sites/timber/lib tags/$1/lib
	cp -r ~/Sites/timber/timber-starter-theme tags/$1/timber-starter-theme
	cp -r ~/Sites/timber/vendor tags/$1/vendor
	cp ~/Sites/timber/LICENSE.txt tags/$1/LICENSE.txt
	cp ~/Sites/timber/README.md tags/$1/README.md
	cp ~/Sites/timber/init.php tags/$1/init.php
	cp ~/Sites/timber/readme.txt tags/$1/readme.txt
	cp ~/Sites/timber/bin/timber.php tags/$1/timber.php
	svn add tags/$1
	cd tags/$1
	svn commit -m "updating to $1"
	cd ~/Sites/timber-wp/trunk
	rm -rf ~/Sites/timber-wp/trunk/lib
	rm -rf ~/Sites/timber-wp/trunk/timber-starter-theme
	rm -rf ~/Sites/timber-wp/trunk/vendor
	cp -r ~/Sites/timber/lib ~/Sites/timber-wp/trunk
	cp -r ~/Sites/timber/timber-starter-theme ~/Sites/timber-wp/trunk
	cp -r ~/Sites/timber/vendor ~/Sites/timber-wp/trunk
	cp ~/Sites/timber/LICENSE.txt ~/Sites/timber-wp/trunk/LICENSE.txt
	cp ~/Sites/timber/README.md ~/Sites/timber-wp/trunk/README.md
	cp ~/Sites/timber/readme.txt ~/Sites/timber-wp/trunk/readme.txt
	cp ~/Sites/timber/init.php ~/Sites/timber-wp/trunk/init.php
	cp ~/Sites/timber/bin/timber.php ~/Sites/timber-wp/trunk/timber.php
	svn commit -m "updating to $1" readme.txt
	svn commit -m "updating to $1" timber.php
}

deploy $1
