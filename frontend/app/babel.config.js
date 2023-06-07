const isDevelopment = process.env.NODE_ENV === 'development';
const isModern = process.argv.includes(`--modern`) || process.env.MODERN;

const isModernDev = isModern && isDevelopment

module.exports = {
  /** https://babeljs.io/docs/en/presets */
  presets: [
    // @vue/babel-preset-app, оборачивает @babel/preset-env ('env')
    [
      "@vue/app", {
        exclude: isModernDev
            ? [
              // разрешено применение async и await в результурующем коде
              'transform-async-to-generator',
              'transform-regenerator',
              'proposal-async-generator-functions'
            ]
            : [],
      }
    ],
    ["@vue/babel-preset-jsx", {}]
  ],
  /**
   * TODO возможность обращения к локально импортированным модулям по короткому имени при отладке в браузере
   *   т. к. получаются длинные имена вроде `lodash__WEBPACK_IMPORTED_MODULE_20__`
   *   Upd: Включение плагина не помогло, вызывает ошибку "exports is not defined",
   *   см. https://github.com/jamietre/babel-plugin-transform-es2015-modules-commonjs-simple/issues/4.
   *   см. https://www.npmjs.com/package/babel-plugin-transform-es2015-modules-commonjs-simple
   */
  //  "plugins": [
  //         // "transform-es2015-arrow-functions",
  //         // "transform-es2015-template-literals",
  //         ["transform-es2015-modules-commonjs-simple", {
  //             "noMangle": true
  //         }]
  //     ],
  //     "sourceMaps": true
};

