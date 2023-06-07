/**
 * Если включена опция WEBPACK_FIX_SOURCE_MAPS, в DevTools браузера на боковой панели
 *   искать исходники для отладки в папке `webpack-sourcecode://`
 *
 * Если сборка происходит неправильно, первым делом попробовать удалить `./node_modules/.cache/`!
 *
 * Используйте `vue inspect` для анализа получившегося конфига
 */

// Настройки:

// Показать warning'и неправильного форматирования кода
const WEBPACK_SHOW_ESLINT_WARNINGS = false;

// Кэш находится в ./node_modules/.cache/, ускоряет повторное построение
const WEBPACK_SPEED_UP_WITH_CACHE = true;

// Чтобы имена файлов из билда не менялись при каждой перестройке
const WEBPACK_HASH_FROM_FILENAME_TO_URL_PARAM = true;

// Отключить, если будут проблемы с source maps
const WEBPACK_FIX_SOURCE_MAPS = true;
const WEBPACK_FIX_SOURCE_MAPS__PROD_TOO = true;

/**
 * В отладочных целях или если файлы теряются
 * от того, что записаны по одному и тому же имени
 **/
const WEBPACK_FIX_SOURCE_MAPS__FORCE_HASH_IN_NAME = false;


// Для отладки
const WEBPACK_REPLACE_FRIENDLY_ERRORS_PLUGIN = false;

// const WEBPACK_NO_HASH_IN_FILENAME_BUT_CUSTOM_URL_PARAM = false;

// Расширенная статистика
const WEBPACK_CUSTOM_STATS = true;

const path = require('path');
const ManifestPlugin = require('webpack-manifest-plugin');
const HardSourceWebpackPlugin = require('hard-source-webpack-plugin');
const StylelintPlugin = require('stylelint-webpack-plugin');
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin');
const fs = require('fs');
const ini = require('ini');
const webpack = require('webpack');
const webpackConfigHelpers = require('./webpackConfigHelpers');


const isProduction = process.env.NODE_ENV === 'production';
const isDevelopment = process.env.NODE_ENV === 'development';
const isMobileApp = process.argv.includes(`--mobileApp`);

/**
 * Режим современного кода без совместимости со старыми браузерами (флаг `--modern` / `npm run dev:modernCode`).
 * Использовать локально, когда source maps не работают и не удаётся поставить точку останова в скрипте.
 *
 * https://cli.vuejs.org/guide/browser-compatibility.html#modern-mode
 **/
const isModern = process.argv.includes(`--modern`);

// построение проходит на сервере
const isBuildOnServer = process.argv.includes(`--onServer`);

const isWatching = process.argv.includes(`--watchBuild`);

const isHmTestBuild = process.argv.includes(`--hmTestBuild`);
const isHmTestServe = process.argv.includes(`--hmTestServe`);

console.log('process.env.NODE_ENV', process.env.NODE_ENV);
// console.log('process.env', process.env);

if (isWatching) {
  console.log(`Watching changes!`);
}

if (isModern) {
  console.log(
    'Modern mode detected: no old browsers compatibility.' + "\n" +
    'See `babel.config.js` and cli.vuejs.org/guide/browser-compatibility.html#modern-mode'
  );
}

const phpConfig = ini.decode(
  fs.readFileSync(
    path.resolve(__dirname, '../../application/settings/config.ini'),
    { encoding: 'utf8' }
  )
);

const devServerAddress = phpConfig['development : production']['webpack.devserver.address'];

let devServerAddressParts = new URL(devServerAddress);

if (!isProduction && !devServerAddress) {
  const error_text = `
  Не найден адресс dev-сервера webpack в application/config.ini.
  Для работы в dev-режиме добавьте
  webpack.devserver.address = 'адрес_dev_сервера'
  в раздел [development : production] этого файла.
  `
  throw new Error(error_text)
}

let publicPath = isProduction || !devServerAddress ? '/frontend/app/' : devServerAddress;

let outputDir = `../../public/frontend/app/`;

if (isMobileApp) {
  console.log('Mobile app mode');
  outputDir = './distMobile';
  publicPath = '/';
}

const config = {
  runtimeCompiler: true, // we need that to parse tags generated by PHP
  publicPath,
  outputDir,
  chainWebpack: webpackChain => {
    // надо добавлять синхронно и в tsconfig.js
    webpackChain.resolve.alias.set('@', path.resolve(__dirname, 'src'));
    webpackChain.resolve.alias.set('~', path.resolve(__dirname, 'srcMobile'));

    if (isMobileApp) {
      webpackConfigHelpers.setPublicTemplateDir(webpackChain, './publicMobile');
    }

    if (!WEBPACK_SHOW_ESLINT_WARNINGS) {
      console.log('WEBPACK_SHOW_ESLINT_WARNINGS=false: setting eslint-loader to quiet mode');
      if (isBuildOnServer) {
        console.log(
          'webpack eslint warnings hiding is disabled due to all errors will be shown as warnings in build on server mode (see .eslintrc)'
        );
      } else {
        webpackConfigHelpers.hideEsLintWarnings(webpackChain);
      }
    }

    // webpackChain.module
    //   .rule('svg')
    //   .use('file-loader')
    //   .loader('vue-svg-loader');

    webpackChain.module.rules.delete("svg");

    if (WEBPACK_REPLACE_FRIENDLY_ERRORS_PLUGIN) {
      webpackChain.plugins.delete("friendly-errors");
    }
  },
  configureWebpack: webpackConfig => {
    let manifestPluginOptions = {};

    // If we start dev build
    if (isDevelopment) {
      webpackConfig.devServer = {
        hot: true,
        contentBase: path.resolve(__dirname, '../../public/'),
        port: devServerAddressParts.port,
        inline: true,
        overlay: {
          warnings: false,
          errors: true
        },
        disableHostCheck: true,
        headers: {
          "Access-Control-Allow-Origin": "*",
          "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
          "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
        },
      }
    }

    // If we start watch build
    if (isWatching) {
      webpackConfig[`watch`] = true;
      webpackConfig[`watchOptions`] = {
        ignored: /node_modules/,
        poll: true,
      }
    }

    /**
     * Плагин для ускорения построения.
     * Отключить при проблемах!
     * Очищать ./node_modules/.cache/ при необходимости
     *
     * https://github.com/mzgoddard/hard-source-webpack-plugin
     * https://medium.com/ottofellercom/0-100-in-two-seconds-speed-up-webpack-465de691ed4a
     */
    if (WEBPACK_SPEED_UP_WITH_CACHE) {
      if (isMobileApp) {
        console.log('Webpack speed up cache is DISABLED for mobile app');
      } else if (isModern) {
        console.log('Webpack speed up cache is DISABLED for modern mode (`--modern`)');
      } else {
        console.log('WEBPACK_SPEED_UP_WITH_CACHE: enabling HardSourceWebpackPlugin');
        webpackConfig.plugins.push(
          new HardSourceWebpackPlugin()
        );
      }
    }

    /**
     * CSS linting
     *
     * https://medium.com/@brtjkzl/linting-css-in-vue-files-6bb20faac0f2
     * https://github.com/webpack-contrib/stylelint-webpack-plugin
     **/
    if (!isBuildOnServer) {
      // TODO понять, почему приводит к ошибке на сервере
      webpackConfig.plugins.push(
        new StylelintPlugin({
          files: ['src/**/*.vue'],
          // warnings instead of errors
          emitError: false,
          emitWarning: true,
          lintDirtyModulesOnly: true,
        })
      );
    }

    webpackConfig.optimization = {...webpackConfig.optimization, runtimeChunk: "single"}

    if (isProduction && WEBPACK_HASH_FROM_FILENAME_TO_URL_PARAM) {
      console.log('WEBPACK_HASH_FROM_FILENAME_TO_URL_PARAM');
      webpackConfigHelpers.hashFromFilenameToUrlParam(webpackConfig, manifestPluginOptions)
    }

    if (WEBPACK_CUSTOM_STATS) {
      console.log('WEBPACK_CUSTOM_STATS');
      webpackConfigHelpers.customStats(webpackConfig, isDevelopment);
    }

    webpackConfig.plugins.push(
      new ManifestPlugin(manifestPluginOptions),
    );

    if (WEBPACK_REPLACE_FRIENDLY_ERRORS_PLUGIN) {
      console.log('WEBPACK_REPLACE_FRIENDLY_ERRORS_PLUGIN');
      webpackConfig.plugins.push(
        new FriendlyErrorsWebpackPlugin(
          {
            additionalTransformers: [
              error => {
                return error
              }
            ],
            additionalFormatters: [
              errors => {
                // errors = errors.filter(e => e.shortMessage)
                // if (errors.length) {
                //   return errors.map(e => e.shortMessage)
                // }
                return errors;
              }
            ]
          }
        )
      );
    }

    let fixSourceMaps = false;

    if (WEBPACK_FIX_SOURCE_MAPS) {
      if (isDevelopment) {
        fixSourceMaps = true;
        console.log('WEBPACK_FIX_SOURCE_MAPS');
      } else { // prod
        if (WEBPACK_FIX_SOURCE_MAPS__PROD_TOO) {
          fixSourceMaps = true;
          console.log('WEBPACK_FIX_SOURCE_MAPS && WEBPACK_FIX_SOURCE_MAPS__PROD_TOO');
        }
      }
    }

    if (fixSourceMaps) {

      if (isDevelopment) {
        /**
         * https://webpack.js.org/configuration/devtool/
         * `source-map`: (по-умолчанию) slowest build, slowest rebuild. Отдельные файлы .map.
         * `eval-source-map`: original source in DevTools, slowest build, fast rebuild.
         *     Отдельные файлы .map не генерируются, не подходит для prod-build'а.
         *     Позволяет отлаживать по выражениям внутри одной строки.
         */
      // webpackConfig.devtool = 'cheap-module-eval-source';
      webpackConfig.devtool = 'eval-source-map';
      // webpackConfig.devtool = 'inline-source-map';
      // webpackConfig.devtool = 'source-map';
      }

      webpackConfigHelpers.fixSourceMaps(
        webpackConfig,
        WEBPACK_FIX_SOURCE_MAPS__FORCE_HASH_IN_NAME
      );
    }

    // Дампы конфига для отладки:
    webpackConfigHelpers.dumpWebpackConfig(webpackConfig);
  },
  css: {
    sourceMap: isDevelopment,
    loaderOptions: {
      sass: {
        includePaths: [path.resolve(__dirname, './node_modules')]
      }
    }
  }
};

// Для построения компонента тестов отдельно для 4g
const HmTestBuildConfig = {
  outputDir: `./HmTestBuild`,
  configureWebpack: {
    plugins: [
      new webpack.DefinePlugin({
        'process.env': {
          'IS_TEST': true
        }
      })
    ]
  }
};

// Для отладки компонента тестов отдельно (для 4g)
const HmTestServeConfig = {
  css: {
    loaderOptions: {
      sass: {
        includePaths: [
          path.resolve(__dirname, "./node_modules"),
        ]
      }
    }
  },

  runtimeCompiler: true,

  configureWebpack: {
    // resolve: {
    //   alias: {
    //     vue: '/js/lib/vuejs/vue.min.js'
    //   }
    // },

    plugins: [
      new webpack.DefinePlugin({
        'process.env': {
          'IS_TEST': true
        }
      })
    ],

    devServer: {
      headers: {
        "Access-Control-Allow-Origin": "*",
        "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
        "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
      },
      // Для показа в локальной сети
      //
      //   (изменить также `webpack.devserver.address` в
      //    `../../application/settings/config.ini`)
      //
      host: devServerAddressParts.hostname,
      sockPort: 8080,
      disableHostCheck: true,
    }//,

    // hashFilenames: false
  },

  filenameHashing: false,
};

if (isHmTestBuild) {
  console.log('Building HmTest...');
  module.exports = HmTestBuildConfig;
} else if (isHmTestServe) {
  console.log('Serving HmTest for legacy 4g...');
  module.exports = HmTestServeConfig;
} else {
  module.exports = config;
}
