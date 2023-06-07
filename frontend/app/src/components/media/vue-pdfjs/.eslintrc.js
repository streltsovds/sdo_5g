module.exports = {
  /**
   * Здесь только патч.
   * За основу будут взяты правила в корне проекта
   **/

  "rules": {
    "prettier/prettier": ["warn", {
      "endOfLine": "auto",
      "trailingComma": "es5",
      "singleQuote": true,
    }],
    "vue/component-name-in-template-casing": "off",
  }
};
