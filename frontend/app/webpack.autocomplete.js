/**
 * Для построения используется `vue.config.js`
 *
 * Этот конфиг нужен только для автодополнения в PhpStorm!
 * Указать его в `Settings > Languages & Frameworks > JavaScript > Webpack`
 */

// const webpack = require("webpack");
const path = require("path");

module.exports = {
  resolve: {
    extensions: [".js", ".json", ".vue"],
    alias: {
      "@": path.resolve(__dirname, "./src"),
      "~": path.resolve(__dirname, "./srcMobile"),
    },
  },
};
