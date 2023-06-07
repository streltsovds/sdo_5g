module.exports = {
  "overrides": [
    {
      "files": ["*.vue"],
      "rules": {
        /** @see https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/html-indent.md */
        "vue/html-indent": ["warn", 2, {
          "baseIndent": 0,
        }],
      },
    },
  ],
}
