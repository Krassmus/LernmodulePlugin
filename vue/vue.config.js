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
  },
  css: {
    loaderOptions: {
      css: {
        // options here will be passed to css-loader
        // Prevent resolving of url(). This allows us to put URLs to Stud.IP
        // assets without breaking our CI (since our CI environment currently
        // does not include a Stud.IP core installation with the assets we
        // would like to import). It also stops us from bundling an unnecessary
        // copy of each Stud.IP asset that we reference by url.
        // See https://stackoverflow.com/a/68453585/7359454
        url: true,
      },
    },
  },
  pages: {
    editor: 'src/editor-main.ts',
    viewer: 'src/viewer-main.ts',
    courseware: 'src/courseware-main.ts',
  },
});
