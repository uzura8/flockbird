#!/bin/sh

set -e
export COMPOSER_PROCESS_TIMEOUT=2000
php composer.phar update
php oil refine install
chmod -R 777 fuel/app/media
chmod -R 777 public/media
chmod -R 777 public/assets/cache
php oil r setupassets
chmod -R 777 public/assets/ccss
php oil r setupdb
php oil r setupmodule
php oil r setupfiles
php oil r user:create sample@example.com password 初期メンバー
php oil r admin::createuser admin password admin@example.com 100
