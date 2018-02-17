#!/usr/bin/env sh

if [[ ! -f composer.lock || ! -d src ]]; then
    if [ ! -f .env.mydevil ]; then
        echo "Env file not found."
        exit 2
    fi;
    echo "You must execute this script from root path of app.";
    exit 1
fi;

mv .env.mydevil .env
rm -rf .idea .git .env.* .directory .gitignore vendor logs public/bundles config/jwt var supervisord.pid public/media/upload/*
 
php71 /usr/local/bin/composer install -n -a --no-progress --no-ansi --no-suggest --no-scripts

php71 bin/console cache:clear
php71 bin/console cache:warmup
gmake generate-jwt-keys

rm -rf ../public_html/bundles
rm -rf ../public_html/media
php71 bin/console assets:install --symlink --relative ../public_html
cp public/* ../public_html
ln -s `pwd`/public/media ../public_html/media

sed -i _backup "s~^const APP_PATH.*$~const APP_PATH = \"../${PWD##*/}/\";~g" ../public_html/index.php && rm ../public_html/index.php_backup

php71 bin/console doctrine:schema:drop --force
php71 bin/console doctrine:schema:create
php71 bin/console doctrine:fixtures:load -n