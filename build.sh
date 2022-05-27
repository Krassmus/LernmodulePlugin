#!/bin/bash
rm -f dist/plugin.zip
cd vue && npm install && npm run build
cd ..
timestamp=$(date "+%Y.%m.%d-%H.%M.%S")
# Stud.IP takes FOREVER to install the plugin if .git or node_modules are included.
# zip -r "dist/plugin-$timestamp.zip" . -x "./.git/*" "./vue/node_modules/*" "./.idea/*" "./dist/*"
zip -r "dist/plugin.zip" . -x "./.git/*" "./vue/node_modules/*" "./.idea/*" "./dist/*"
echo -e '@ config.prod.php\n@=config.php' | zipnote -w dist/plugin.zip
