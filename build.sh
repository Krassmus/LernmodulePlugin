#!/bin/bash
set -o pipefail
timestamp=$(date "+%Y.%m.%d-%H.%M.%S") &&
rm -f "dist/*" &&
cd vue && 
npm install && 
npm run build && 
cd .. &&
# Zip up the whole plugin directory's contents, excluding certain folders.
# Stud.IP will take FOREVER to install the plugin if .git or node_modules are included.
zip -r "dist/plugin-$timestamp.zip" . -x "./.git/*" "./vue/node_modules/*" "./.idea/*" "./dist/*" &&
# In the zip, rename config.prod.php to config.php.
echo -e '@ config.prod.php\n@=config.php' | zipnote -w "dist/plugin-$timestamp.zip"
