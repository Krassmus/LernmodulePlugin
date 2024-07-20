const { defineConfig } = require('@vue/cli-service');
module.exports = defineConfig({
  // Allow hot module reloading to work within Stud.IP.
  // You have to set publicPath so that the client knows to load HMR updates
  // from localhost:8080
  publicPath:
    process.env.NODE_ENV === 'development' ? 'http://localhost:8080' : '',
  transpileDependencies: true,
  filenameHashing: false,
  configureWebpack: {
    devtool: 'source-map',
    experiments: {
      topLevelAwait: true,
    },
  },
  pages: {
    editor: 'src/editor-main.ts',
    viewer: 'src/viewer-main.ts',
    courseware: 'src/courseware-main.ts',
  },
});
