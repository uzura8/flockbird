#!/bin/sh

cp config.php.sample config.php
git submodule init
git submodule foreach 'git co 1.6/master'
git submodule update
php composer.phar update
php oil refine install
chmod -R 777 public/media/*

