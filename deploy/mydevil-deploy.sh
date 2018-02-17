#!/usr/bin/env sh

mv /tmp/mydevil.env .env
rm -rf .idea .git .env.* .directory .gitignore vendor logs public/bundles config/jwt var supervisord.pid public/media/upload/*

rsync -r --delete-after --exclude 'docker' 'tests' 'features' --quiet $TRAVIS_BUILD_DIR/ k911-main@s11.mydevil.net:/home/k911-main/domains/knit-test-api.tk/app-api-$APP_VERSION
