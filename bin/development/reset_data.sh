#!/bin/sh

php oil r setupdb:reset
sudo php oil r filecleaner
php oil r createuser sample@example.com password 初期メンバー
php oil r admin::createuser admin password admin@example.icom 100

