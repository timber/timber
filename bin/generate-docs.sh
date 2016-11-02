mkdir docs/markdowndocs
cd vendor/jarednova/markdowndocs/
composer install

cd ../../..

./phpdocs-md generate Timber\\Archives > ./docs/markdowndocs/_timberarchives.md
./phpdocs-md generate Timber\\Comment > ./docs/markdowndocs/_timbercomment.md
./phpdocs-md generate Timber\\Image > ./docs/markdowndocs/_timberimage.md
./phpdocs-md generate Timber\\Menu > ./docs/markdowndocs/_timbermenu.md
./phpdocs-md generate Timber\\MenuItem > ./docs/markdowndocs/_timbermenuitem.md
./phpdocs-md generate Timber\\Pagination > ./docs/markdowndocs/_timberpagination.md
./phpdocs-md generate Timber\\Post > ./docs/markdowndocs/_timberpost.md
./phpdocs-md generate Timber\\PostQuery > ./docs/markdowndocs/_timberpostquery.md
./phpdocs-md generate Timber\\PostPreview > ./docs/markdowndocs/_timberpostpreview.md
./phpdocs-md generate Timber\\Site > ./docs/markdowndocs/_timbersite.md
./phpdocs-md generate Timber\\Theme > ./docs/markdowndocs/_timbertheme.md
./phpdocs-md generate Timber\\Term > ./docs/markdowndocs/_timberterm.md
./phpdocs-md generate Timber\\User > ./docs/markdowndocs/_timberuser.md

./phpdocs-md generate Timber\\Timber > ./docs/markdowndocs/_timber.md

./phpdocs-md generate Timber\\Helper > ./docs/markdowndocs/_timberhelper.md
./phpdocs-md generate Timber\\ImageHelper > ./docs/markdowndocs/_timberimagehelper.md
./phpdocs-md generate Timber\\URLHelper > ./docs/markdowndocs/_timberurlhelper.md
./phpdocs-md generate Timber\\TextHelper > ./docs/markdowndocs/_timbertexthelper.md

cp docs/wiki/*.md ../slate/source/includes

mv docs/markdowndocs/*.md ../slate/source/includes
