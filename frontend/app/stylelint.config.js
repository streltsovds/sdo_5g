/** https://stackoverflow.com/a/58672961 */
module.exports = {
  // "__comments__processors": "used instead of `stylelint-processor-html` because of line numbers issue: https://github.com/vuejs/vue-loader/issues/303",
  // "processors": ["@mapbox/stylelint-processor-arbitrary-tags"],
  "extends": "stylelint-config-recommended-scss",
  "rules": {
    "color-hex-length": null,
    "comment-empty-line-before": ["always", {
      "except": ["first-nested"],
      "ignore": ["after-comment"]
    }],
    "declaration-empty-line-before": null,
    "no-empty-source": null,
    "rule-empty-line-before": null,
    "selector-pseudo-element-colon-notation": null,
    'selector-pseudo-element-no-unknown': [
      true,
      {
        ignorePseudoElements: ['v-deep']
      },
    ],
  },
};
