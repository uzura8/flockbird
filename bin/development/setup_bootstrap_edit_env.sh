#!/bin/sh

BOOTSTRAP_DIR='bootstrap'

if [ ! -d fuel ]; then
  echo "This is not project root dir."
  exit 1
fi

cd ./fuel/app/tmp
if [ -d $BOOTSTRAP_DIR ]; then
  rm -rf $BOOTSTRAP_DIR
fi
git clone https://github.com/twbs/bootstrap.git
cp ../../../data/sample/bootstrap/index.html $BOOTSTRAP_DIR/
cd ../../../public
if [ -d $BOOTSTRAP_DIR ]; then
  rm -rf $BOOTSTRAP_DIR
fi
ln -s ../fuel/app/tmp/$BOOTSTRAP_DIR ./
cd $BOOTSTRAP_DIR
npm install

