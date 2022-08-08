#!/bin/bash
set -o pipefail
timestamp=$(date "+%Y-%m-%d_%H-%M-%S") &&
git_hash=$(git rev-parse --short HEAD) &&
cd vue &&
npm install &&
npm run gettext:compile && # Compile translations for Vue/Typescript
npm run build &&
cd ../locale &&
./compileTranslation && # Compile translations for PHP
cd .. &&
# Zip up the whole plugin directory's contents, excluding certain folders.
# Stud.IP will take FOREVER to install the plugin if .git or node_modules are included.
zip -r "dist/lernmodule-plugin-$timestamp-$git_hash.zip" . -x "./.git/*" "./vue/node_modules/*" "./.idea/*" "./dist/*"
