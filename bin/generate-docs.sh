#phpdoc --template="xml"
#mkdir /srv/www/timber/docs/markdown
#vendor/evert/phpdoc-md/bin/phpdocmd docs/output/structure.xml /srv/www/timber/docs/markdown
mkdir docs/markdowndocs
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberMenuItem > docs/markdowndocs/timbermenuitem.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberPost > docs/markdowndocs/timberpost.md

cd /srv/www/slate
grunt
mv /srv/www/timber/docs/markdowndocs/*.md /srv/www/slate/source/includes
