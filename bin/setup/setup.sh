#!/bin/sh

git submodule init
git submodule foreach 'git checkout 1.8/develop'
git submodule update
git submodule foreach 'git fetch;git checkout 1.8/develop'
php composer.phar update
php oil refine install
chmod -R 777 fuel/app/media/*
chmod -R 777 public/media/*
php oil r setupdb
php oil r createuser sample@example.com password 初期メンバー
php oil r admin::createuser admin password admin@example.icom 100

