#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
WP_VERSION=${4-master}

set -ex

# set up a WP install
WP_CORE_DIR=/tmp/wordpress/
mkdir -p $WP_CORE_DIR
wget -nv -O /tmp/wordpress.tar.gz https://github.com/WordPress/WordPress/tarball/$WP_VERSION
tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

# set up testing suite
svn co --ignore-externals --quiet http://unit-tests.svn.wordpress.org/trunk/ $WP_TESTS_DIR

cd $WP_TESTS_DIR
cp wp-tests-config-sample.php wp-tests-config.php
sed -i "s:dirname( __FILE__ ) . '/wordpress/':'$WP_CORE_DIR':" wp-tests-config.php
sed -i "s/yourdbnamehere/$DB_NAME/" wp-tests-config.php
sed -i "s/yourusernamehere/$DB_USER/" wp-tests-config.php
sed -i "s/yourpasswordhere/$DB_PASS/" wp-tests-config.php

# create database
mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"
