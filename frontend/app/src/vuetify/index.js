import Vue from 'vue';

/**
 * When importing from vuetify/lib, the necessary styles are automatically imported for you.
 * https://vuetifyjs.com/en/getting-started/quick-start
 * */
// import Vuetify from 'vuetify/lib';

// https://vuetifyjs.com/en/getting-started/quick-start#vue-cli-full-installation
import Vuetify from 'vuetify'

import 'vuetify/dist/vuetify.min.css'
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader
import defaultColorsLight from "./defaultColorsLight";

/**
 * deprecated after move to vuetify 2.0
 * @see https://stackoverflow.com/a/57284731
 */
// import "./main.styl";

export default (i18n) => {

  const vuetifyConfig = {
    theme: {
      themes: {

      }
    },
    // Про подключение перевода:
    // https://v15.vuetifyjs.com/en/framework/internationalization#vue-i18n
    lang: {
      // locales: { ru },
      current: "ru",
      t: (key, ...params) => i18n.t(key, params)
    },
  //   options: {
  //     minifyTheme: function(css) {
  //       return process.env.NODE_ENV === "production"
  //         ? css.replace(/[\s|\r\n|\r|\n]/g, "")
  //         : css;
  //     }
  //   }
  };

  if (window.__HM && window.__HM.themeColors) {
    if ( window.__HM.darkTheme) {
      vuetifyConfig.theme.themes.dark = {...window.__HM.themeColors};
      vuetifyConfig.theme.dark = true;
    } else {
      vuetifyConfig.theme.themes.light = {...window.__HM.themeColors};
    }
  } else {
    vuetifyConfig.theme.themes.light = defaultColorsLight;
  }

  Vue.use(Vuetify);

  return new Vuetify(vuetifyConfig);
}
