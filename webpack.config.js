const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath(__dirname+'/themes/default/build')
    .setPublicPath('/')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSassLoader()
    .enableSingleRuntimeChunk()
    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('index', __dirname+'/themes/default/assets/index.js')
;

module.exports = Encore.getWebpackConfig();
