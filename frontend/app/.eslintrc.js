const lodash = require('lodash');

// удалить null-значения
const compact = lodash.compact;

require('./node_modules_patches/for-eslint');

// построение проходит на сервере
const isBuildOnServer = process.argv.includes(`--onServer`);

module.exports = {
  /**
   * По-умолчанию базовые правила рекурсивно ищутся из папок выше, до корня диска.
   * "root": true означает не уходить при поиске правил выше этой папки
   */
  "root": true,

  // "parserOptions": {
  //   "ecmaVersion": 9,
  //   "sourceType": "module",
  //   "parser": "babel-eslint"
  // },

  parserOptions: {
    ecmaVersion: 9,
    sourceType: 'module',
    parser: '@typescript-eslint/parser'
  },

  "env": {
    "browser": true,
    "node": true,
  },

  "extends": [
    "eslint:recommended",
    "plugin:vue/recommended",
    "prettier",
    /**
     * закомментировано, т. к. ниже у нас отключается prettier для vue-файлов
     * @see https://github.com/prettier/eslint-config-prettier/blob/master/vue.js
     */
    // "prettier/vue"
    '@vue/typescript'
  ],

  "plugins": compact(
    [
      "vue",
      "prettier",
      "vuetify",
      "jest",
      isBuildOnServer ? "only-warn" : null,
    ],
  ),

  "rules": {
    "prettier/prettier": ["warn", {
      "singleQuote": true,
      "endOfLine": "auto",
      "trailingComma": "es5"
    }],
    "import/no-unresolved": 0,
    "import/no-unassigned-import": 0,

    "vue/component-name-in-template-casing": ["error",
      "kebab-case",
      {
        "ignores": [
        ]
      }
    ],
    "vue/multiline-html-element-content-newline": ["error", {
      "allowEmptyLines": true,
    }],
    "no-console": "off",
    "vue/no-v-html": "warn",
    "vuetify/no-deprecated-classes": "error",
    "vuetify/grid-unknown-attributes": "error",
  },

  "overrides": [
    /**
     * для vue-файлов prettier отключается и используются возможности eslint-plugin-vue для управления отступами
     * @see https://vuejs.github.io/eslint-plugin-vue/rules/
     */
    {
      "files": ["*.vue"],
      "rules": {
        "prettier/prettier": "off",
        "indent": "off",

        /** @see https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/html-indent.md */
        "vue/html-indent": ["warn", 2, {
          "attribute": 1,
          "baseIndent": 1,
          "closeBracket": 0,
          "alignAttributesVertically": true,
          "ignores": []
        }],

        "vue/multiline-html-element-content-newline": "warn",

        // На сервере плагин only-warn все ошибки переводит в разряд предупреждений (см. выше)

        // "vuetify/no-deprecated-classes": isBuildOnServer ? "warn" : "error",
        // "vuetify/no-legacy-grid": isBuildOnServer ? "warn" : "error",
        // "no-unused-vars": isBuildOnServer ? "warn" : "error",

        /** @see https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/script-indent.md */
        "vue/script-indent": ["warn", 2, {
          "baseIndent": 0,
          /* Отступ case по сравнению с switch */
          "switchCase": 1,
          "ignores": []
        }],

        /** @see https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/max-attributes-per-line.md */
        "vue/max-attributes-per-line": ["warn", {
          "singleline": 3,
          "multiline": {
            "max": 1,
            "allowFirstLine": true
          }
        }]
      },
    }
  ],
};
