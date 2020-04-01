#!/bin/bash

rm -rf ./deploy
rm -rf /tmp/humhub-deploy/

cwd=$(pwd)

cp -r ./ /tmp/humhub-deploy/
mkdir -p ./deploy/onlyoffice
cd /tmp/humhub-deploy/

rm -rf ./.git/
rm ./.gitignore
rm ./pack.sh

mkdir docs

mv ./LICENCE.md ./docs/LICENCES.md
mv ./AUTHORS.md ./docs/AUTHORS.md
mv ./CHANGELOG.md ./docs/CHANGELOG.md

node pack.js
rm ./pack.js
rm ./README.md

mv ./* $cwd/deploy/onlyoffice/