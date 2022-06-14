const { defineConfig } = require('@vue/cli-service');
// This plugin type-checks the contents of <template> tags in .vue files :)
const VueTsCheckerPlugin = require('@juit/vue-ts-checker').VueTsCheckerPlugin;

module.exports = defineConfig({
  transpileDependencies: true,
  filenameHashing: false,
  configureWebpack: {
    devtool: 'source-map',
  },
  chainWebpack: (config) => {
    // We don't need the "fork-ts-checker" plugin anymore, as "vue-ts-checker"
    // also checks all of the TypeScript included in each compilation!
    config.plugins.delete('fork-ts-checker');
    // Let the "vue-ts-checker" plugin take care of checking Vue and TypeScript
    config.plugin('vue-ts-checker').use(new VueTsCheckerPlugin());
  },
  pages: {
    editor: 'src/editor-main.ts',
    viewer: 'src/viewer-main.ts',
  },
});
