#!/bin/sh

if [ ! -d fuel ]; then
  echo "This is not project root dir."
  exit 1
fi
cd fuel/app/tmp/bootstrap/
grunt dist
cp dist/css/bootstrap.min.css public/assets/css/bootstrap.uzura.min.css
