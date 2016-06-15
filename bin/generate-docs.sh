#mkdir docs/markdowndocs
cd vendor/jarednova/markdowndocs/
#composer install
cd ../../..

pwd

./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate --bootstrap=bin/bootstrap-docs.php Timber\\Archives > ./docs/markdowndocs/_timberarchives.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=bin/bootstrap-docs.php Timber\\Comment > ./docs/markdowndocs/_timbercomment.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=bin/bootstrap-docs.php Timber\\Image > ./docs/markdowndocs/_timberimage.md
./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=bin/bootstrap-docs.php Timber\Menu > ./docs/markdowndocs/_timbermenu.md
# ./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=vendor/autoload.php Timber\MenuItem > docs/markdowndocs/_timbermenuitem.md
# ./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=vendor/autoload.php Timber\Post > docs/markdowndocs/_timberpost.md
# ./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=vendor/autoload.php Timber\Site > docs/markdowndocs/_timbersite.md
# ./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=vendor/autoload.php Timber\Theme > docs/markdowndocs/_timbertheme.md
# ./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=vendor/autoload.php Timber\Term > docs/markdowndocs/_timberterm.md
# ./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=vendor/autoload.php Timber\User > docs/markdowndocs/_timberuser.md

./vendor/jarednova/markdowndocs/bin/phpdoc-md generate --bootstrap=timber.php TimberHelper > docs/markdowndocs/_timberhelper.md

# cp docs/wiki/*.md ../slate/source/includes

#mv docs/markdowndocs/*.md ../slate/source/includes
