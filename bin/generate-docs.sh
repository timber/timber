phpdoc --template="xml"
mkdir /srv/www/timber/docs/markdown
vendor/evert/phpdoc-md/bin/phpdocmd docs/output/structure.xml /srv/www/timber/docs/markdown
mv /srv/www/timber/docs/markdown/*.md /srv/www/slate/source/includes
