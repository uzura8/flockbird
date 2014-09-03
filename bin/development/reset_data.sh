#!/bin/sh

php oil r setupdb:reset
sudo php oil r filecleaner
php oil r createuser sample@example.com password 初期メンバー
php oil r createuser sample2@example.com password メンバー2
php oil r createuser sample3@example.com password メンバー3
php oil r createuser sample4@example.com password メンバー4
php oil r createuser sample5@example.com password メンバー5
php oil r createuser sample6@example.com password メンバー6
php oil r createuser sample7@example.com password メンバー7
php oil r createuser sample8@example.com password メンバー8
php oil r createuser sample9@example.com password メンバー9
php oil r createuser sample10@example.com password メンバー10
php oil r admin::createuser admin password admin@example.icom 100

