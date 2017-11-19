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
rm -rf .idea .git .env.* .directory .gitignore vendor logs public/bundles config/jwt var supervisord.pid

if [ ! -f composer.phar ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
else
    echo "Found composer.phar.."
fi

php71 composer.phar install -o --no-scripts
php71 bin/console cache:clear
php71 bin/console cache:warmup
gmake generate-jwt-keys
rm -rf ../public_html/bundles
php71 bin/console assets:install --symlink --relative ../public_html
cp public/* ../public_html
sed -i _backup "s~^const APP_PATH.*$~const APP_PATH = \"../${PWD##*/}/\";~g" ../public_html/index.php && rm ../public_html/index.php_backup

php71 bin/console doctrine:schema:drop --force
php71 bin/console doctrine:schema:create
php71 bin/console doctrine:fixtures:load -n