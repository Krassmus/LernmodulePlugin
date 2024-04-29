#!/bin/bash
set -euxo pipefail # explanation at https://gist.github.com/mohanpedala/1e2ff5661761d3abd0385e8223e16425
if [[ ! "$(node -v)" =~ v16\..* ]] || [[ ! "$(npm -v)" =~ 8\..* ]]; then
  echo "Please use node v16 and npm 8"
  exit 1;
fi;
timestamp=$(date "+%Y-%m-%d_%H-%M-%S") &&
git_hash=$(git rev-parse --short HEAD) &&
cd vue && # Build Vue3 project
npm install &&
npm run gettext:compile && # Compile translations for Vue3
npm run build &&
cd ../courseware-blocks-vue2 && # Build vue2 courseware block project
npm install &&
npm run build &&
cd ../locale && # Compile translations for PHP
./compileTranslation &&
cd .. &&
# Zip up the whole plugin directory's contents, excluding certain folders.
# Stud.IP will take FOREVER to install the plugin if .git or node_modules are included.
zip -r "dist/lernmodule-plugin-$timestamp-$git_hash.zip" . -x "./.git/*" "*/node_modules/*" \
 "./.idea/*" "./dist/*" "./.gitlab-ci.yml"
