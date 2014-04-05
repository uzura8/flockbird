#!/bin/sh

#if [ ! -e config.php ]; then
#cp config.php.sample config.php
#fi
git submodule init
git submodule foreach 'git checkout 1.7/master'
git submodule update
git submodule foreach 'git fetch;git checkout 1.7/master'
php composer.phar update
php oil refine install
chmod -R 777 public/media/*

