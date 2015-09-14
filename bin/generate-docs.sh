mkdir docs/markdowndocs

cd vendor/jarednova/markdowndocs/
composer install
cd /srv/www/timber

./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberArchives > docs/markdowndocs/timberarchives.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberComment > docs/markdowndocs/timbercomment.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberImage > docs/markdowndocs/timberimage.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberMenu > docs/markdowndocs/timbermenu.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberMenuItem > docs/markdowndocs/timbermenuitem.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberPost > docs/markdowndocs/timberpost.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberSite > docs/markdowndocs/timbersite.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberTheme > docs/markdowndocs/timbertheme.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberTerm > docs/markdowndocs/timberterm.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberUser > docs/markdowndocs/timberuser.md

./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberHelper > docs/markdowndocs/timberhelper.md

cp /srv/www/timber/docs/wiki/*.md /srv/www/slate/source/includes

mv /srv/www/timber/docs/markdowndocs/*.md /srv/www/slate/source/includes
