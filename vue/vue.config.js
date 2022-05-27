const { defineConfig } = require('@vue/cli-service');
module.exports = defineConfig({
  transpileDependencies: true,
  filenameHashing: false,
  configureWebpack: {
    devtool: 'source-map'
  },
  pages: {
    'editor': 'src/editor-main.ts',
    'viewer': 'src/viewer-main.ts'
  }
});
