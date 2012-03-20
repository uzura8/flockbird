#!/bin/sh

cp config.php.sample config.php
git submodule init
git submodule update
php oil refine install
