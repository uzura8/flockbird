#!/bin/sh

php oil r setupdb:reset
sudo php oil r filecleaner
php oil r user:create sample@example.com password 初期メンバー
php oil r user:create sample2@example.com password メンバー2
php oil r user:create sample3@example.com password メンバー3
php oil r user:create sample4@example.com password メンバー4
php oil r user:create sample5@example.com password メンバー5
php oil r user:create sample6@example.com password メンバー6
php oil r user:create sample7@example.com password メンバー7
php oil r user:create sample8@example.com password メンバー8
php oil r user:create sample9@example.com password メンバー9
php oil r user:create sample10@example.com password メンバー10
php oil r admin::createuser admin password admin@example.icom 100

