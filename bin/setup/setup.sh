#!/bin/sh

set -e
php composer.phar update
php oil refine install
chmod -R 777 fuel/app/media
chmod -R 777 public/media
chmod -R 777 public/assets/ccss
cd fuel/app/assets/less/
ln -s ../../../vendor/twbs/bootstrap/less bootstrap
cd ../../../../
php oil r setupassets
php oil r setupdb
php oil r setupmodule
php oil r user:create sample@example.com password 初期メンバー
php oil r admin::createuser admin password admin@example.icom 100
