mkdir docs/markdowndocs

./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberArchives > docs/markdowndocs/timberarchives.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberComment > docs/markdowndocs/timbercomment.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberImage > docs/markdowndocs/timberimage.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberMenu > docs/markdowndocs/timbermenu.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberMenuItem > docs/markdowndocs/timbermenuitem.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberPost > docs/markdowndocs/timberpost.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberSite > docs/markdowndocs/timbersite.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberTheme > docs/markdowndocs/timbertheme.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberTerm > docs/markdowndocs/timberterm.md
./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberUser > docs/markdowndocs/timberuser.md

./vendor/victorjonsson/markdowndocs/bin/phpdoc-md generate TimberHelper > docs/markdowndocs/timberhelper.md

mv /srv/www/timber/docs/markdowndocs/*.md /srv/www/slate/source/includes