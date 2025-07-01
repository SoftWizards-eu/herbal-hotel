var dotenv = require('dotenv').config({path: __dirname + '/../../../../../.env.local'});
var Encore = require('@symfony/webpack-encore');
// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('../public/build/')
    // public path used by the web server to access the output path
    .setPublicPath(Encore.isProduction() ? '.' : '/build/admin')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build')
  
    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('admin', './js/admin.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    //.splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
       
    })
    .configureBabel(config => {
	config.exclude = /ckeditor\/build\/ckeditor/
    })
    .configureDevServerOptions(option => {
       //option.firewall = false;
       option.watchFiles = [__dirname + '/templates/**/*']
    })
    .enableVueLoader()

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    .enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/admin.js')
;
module.exports = Encore.getWebpackConfig()
if (Encore.isProduction()) {
  let processOutput = module.exports.plugins[9].options.processOutput
  module.exports.plugins[9].options.processOutput = function() {
    let res = processOutput.apply(null, arguments)
    res = JSON.parse(res)
    console.log(typeof res)
    for (const [entry, kinds] of Object.entries(res.entrypoints)) {
       for(const [kind, files] of Object.entries(kinds)) {
          var tmp = []
          files.forEach(f => {
	     tmp.push(f.replace('./', 'bundles/w3desadmin/build/'))
	  })
          res.entrypoints[entry][kind] = tmp
       }
    }
    return JSON.stringify(res, null, 2)
  }
  module.exports.plugins[2].options.publicPath = 'bundles/w3desadmin/build/'
}
module.exports.resolve.alias = {
     ...module.exports.resolve.alias,
    '@': __dirname + '/js',
    '~': __dirname + ''
}
