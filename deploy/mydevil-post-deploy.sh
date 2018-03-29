#!/usr/bin/env sh
cd /home/k911-main/domains/knit-test-api.tk/app-api-$APP_VERSION

php72 bin/console cache:clear
php72 bin/console cache:warmup
gmake generate-jwt-keys

rm -rf ../public_html/bundles
rm -rf ../public_html/media
php72 bin/console assets:install --symlink --relative ../public_html
cp public/* ../public_html
ln -s `pwd`/public/media ../public_html/media

sed -i _backup "s~^const APP_PATH.*$~const APP_PATH = \"../${PWD##*/}/\";~g" ../public_html/index.php && rm ../public_html/index.php_backup

php72 bin/console doctrine:schema:drop --force
php72 bin/console doctrine:schema:create
APP_REQUEST_CONTEXT_HOST=knit-test-api.tk APP_REQUEST_CONTEXT_SCHEME=https php72 bin/console doctrine:fixtures:load -n
