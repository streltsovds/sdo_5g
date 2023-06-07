const path = require('path');
const fs = require('fs');
const lodash = require('lodash');

const merge = lodash.merge;

module.exports = {
  fixSourceMaps(webpackConfig, sourceWithHash) {
    this.dump('sourceMapping.txt', '');

    /**
     * @see https://github.com/vuejs/vue-cli/issues/2978 - ideas
     * @see https://www.mistergoodcat.com/post/the-joy-that-is-source-maps-with-vuejs-and-typescript - theory
     **/
    webpackConfig.output.devtoolModuleFilenameTemplate = info => {
      // // posix, чтобы на windows правильно строилось
      let resPath = path.posix.normalize(info.resourcePath);

      // не уверен в способе определения typescript
      let isTypeScriptOriginalSource =
        info.shortIdentifier.match(/^\.\/\S*?\.tsx?$/) ||
        (info.query.match(/vue/) &&
          info.query.match(/lang=ts/) &&
          !info.allLoaders.match(/babel-loader/)
        );

      /** For vue-loader 15+, the check should use query instead of allLoaders (thanks, @rndmerle). */
      let generated = info.allLoaders && !isTypeScriptOriginalSource;
      // let generated = info.query && !isTypeScript;

      // let isTypeScriptAddon = isTypeScript ? '_TS_' : '';

      // let outputModuleName = `webpack-sourcecode:///${resPath}?${info.hash}&${isTypeScriptAddon}`;
      let outputModuleName = `webpack-sourcecode:///${resPath}`;

      if (sourceWithHash) {
        outputModuleName = outputModuleName + '?' + info.hash;
      }

      if (generated) {
        outputModuleName = `webpack-generated:///${resPath}?${info.hash}`;
      }

      this.dump(
        'sourceMapping.txt',
        {
          ...info,
          normalizedResourcePath: resPath,
          outputModuleName,
          generated,
          isTypeScript: isTypeScriptOriginalSource,
        },
        true
      );

      return outputModuleName;
    };

    webpackConfig.output.devtoolFallbackModuleFilenameTemplate = 'webpack-fallback:///[resource-path]?[hash]';

    webpackConfig.module.rules.push({
      test: /\.svg$/,
      loader: 'vue-svg-loader',
    });
  },


  hashFromFilenameToUrlParam: function(webpackConfig, manifestPluginOptions) {
    let wpPlugins = webpackConfig.plugins;
    // original: "js/[name].[contenthash:8].js"
    webpackConfig.output.filename = "js/[name].js";
    // chunk file name to be required in entry files
    webpackConfig.output.chunkFilename = "js/[name].js?contenthash=[contenthash:8]";

    // // see https://github.com/webpack/webpack/issues/6598#issuecomment-383396148
    // // see https://www.npmjs.com/package/webpack-chunk-rename-plugin
    // // chunk-* - файлы
    // TODO не работает, остаются записи вида "css/chunk-0075cd9b.css?contenthash=cfac4b33": "/frontend/app/css/chunk-0075cd9b.css?contenthash=cfac4b33", в manifest.json
    // const ChunkRenamePlugin = require("webpack-chunk-rename-plugin");
    //
    // webpackConfig.plugins.push(
    //   new ChunkRenamePlugin({
    //     initialChunksWithEntry: true,
    //   })
    // );

    // TODO тоже не работает - возможно это необходимый механизм для include чанков внутри файлов
    // let WebpackRenameChunkPlugin = require('webpack-renamechunk-plugin');
    //
    // webpackConfig.plugins.push(
    //   new WebpackRenameChunkPlugin({
    //     // chunk file name to be created actually
    //     renameChunkFileName: 'js/[name].js'
    //   })
    // );

    // css
    // TODO как сделать проще?
    for (let pi in wpPlugins) {
      let plugin = wpPlugins[pi];

      if (!plugin.options) {
        continue;
      }

      let ops = plugin.options;

      if (!ops.filename) {
        continue
      }

      if (ops.filename.startsWith('css/')) {
        // original: "css/[name].[contenthash:8].css",
        ops.filename = "css/[name].css";
        // original: "css/[name].[contenthash:8].css"
        ops.chunkFilename = "css/[name].css?contenthash=[contenthash:8]"
      }
    }

    // runtime.js
    let customHash = (new Date).getTime();

    manifestPluginOptions.map = function(item) {
      // console.log('in manifestPluginOptions.map:');
      // console.dir(item);
      if (['runtime.js', 'runtime.js.map'].includes(item.name)) {
        item.path = item.path + '?customhash=' + customHash;
      }
      return item;
    };

    // TODO можно будет попробовать такой вариант, если при WEBPACK_HASH_FROM_FILENAME_TO_URL_PARAM
    //   с подругрузкой chunk'ов будут проблемы
    //
    // if (isProduction && WEBPACK_NO_HASH_IN_FILENAME_BUT_CUSTOM_URL_PARAM) {
    //   let customHash = (new Date).getTime();
    //
    //   console.log('WEBPACK_NO_HASH_IN_FILENAME_BUT_CUSTOM_URL_PARAM');
    //
    //   webpackConfig.output.filename = "js/[name].js";
    //   // chunk file name to be required in entry files
    //   webpackConfig.output.chunkFilename = "js/[name].js";
    //
    //   // css
    //   // TODO как сделать проще?
    //   for (let pi in wpPlugins) {
    //     let plugin = wpPlugins[pi];
    //     if (!plugin.options) {
    //       continue;
    //     }
    //     let opts = plugin.options;
    //     if (!opts.filename) {
    //       continue
    //     }
    //     if (opts.filename.startsWith('css/')) {
    //       // original: "css/[name].[contenthash:8].css",
    //       opts.filename = "css/[name].css";
    //       // original: "css/[name].[contenthash:8].css"
    //       opts.chunkFilename = "css/[name].css"
    //     }
    //   }
    //
    //   manifestPluginOptions.map = function(item) {
    //     // console.log('in manifestPluginOptions.map:');
    //     // console.dir(item);
    //     // if (['runtime.js', 'runtime.js.map'].includes(item.name)) { }
    //     item.path = item.path + '?customhash=' + customHash;
    //     return item;
    //   };
    // }
  },

  hideEsLintWarnings: function(webpackChain) {
    // debug: webpackChain.toString()
    webpackChain.module
      .rule("eslint")
      .use("eslint-loader")
      .tap(esLintOpts =>
        merge(esLintOpts, {
          quiet: true,
        })
      );
    // let wpRules = webpackChain.module.rules;
    //
    // // TODO как сделать проще?
    // for (let ri in wpRules) {
    //   let uses = wpRules[ri].use;
    //
    //   // console.dir(uses);
    //   for (let i in uses) {
    //     let use = uses[i];
    //     // console.dir(use);
    //     // console.log(use.loader);
    //     if (use.loader == 'eslint-loader') {
    //       // https://github.com/webpack-contrib/eslint-loader#quiet-default-false
    //       // Loader will process and report errors only and ignore warnings if this option is set to true
    //       use.options.quiet = true;
    //       // console.dir(use);
    //     }
    //   }
    // }
  },

  customStats: function(webpackConfig, isDevelopment) {
    // https://webpack.js.org/configuration/stats/
    let webpackCustomStats = {
      // copied from `'minimal'`
      all: false,
      modules: true,
      maxModules: 0,
      errors: true,
      warnings: true,
      // our additional options
      moduleTrace: true,
      errorDetails: true,
      timings: true,
    };

    if (isDevelopment) {
      webpackConfig.devServer.stats = webpackCustomStats;
    } else {
      webpackConfig.stats = webpackCustomStats;
    }
  },

  dump: function(fileName, objectToDump, append = false) {
    const pathNodeModulesCache = path.resolve(__dirname, 'node_modules', '.cache');

    fs.mkdirSync(pathNodeModulesCache, { recursive: true });

    let _path = path.resolve(pathNodeModulesCache, fileName);
    let content = JSON.stringify(objectToDump, null, 2);

    if (append) {
      fs.appendFileSync(_path, content);
    } else {
      fs.writeFileSync(_path, content);
    }
  },

  dumpWebpackConfig: function(webpackConfig) {
    this.dump('webpackConfigModules.json', webpackConfig.module);
    this.dump('webpackConfig.json', webpackConfig);
  },

  // https://github.com/vuejs/vue-cli/issues/3184
  setPublicTemplateDir(webpackChain, publicTemplateDir) {
    webpackChain
      .plugin('html')
      .tap(
        args => {
          args[0].template = path.resolve(publicTemplateDir + "/index.html");
          return args;
        }
      );

    webpackChain
      .plugin('copy')
      .tap(
        ([pathConfigs]) => {
          pathConfigs[0].from = path.resolve(publicTemplateDir);
          return [pathConfigs]
        }
      )
  }
};
