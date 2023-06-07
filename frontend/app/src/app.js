import Vue from "vue";
import monkeyPatchVue, {vueComponentsImported} from "@/libs/monkeyPatch/vue";
import monkeyPatchVuetifyComponents from "@/libs/monkeyPatch/vuetifyComponents.ts";
import newVuetify from "./vuetify";
import VueCompositionApi from "@vue/composition-api";
import instanceI18n from "./i18n/instance";
import "./scss/app.scss";
import { composeComputed } from "./utilities";
import GlobalActions from "./store/modules/global/const/actions";

import addPx from "@/utilities/addPx";

import tinymce from "tinymce";

window.tinymce = tinymce;
window.tinymce.baseURL = '/tinymce/';

let locale = "ru";

// Разрешить отладку в Vue Dev Tools для production
Vue.config.devtools = true;

monkeyPatchVue(Vue);

/**
 * деректива прокртуки
 */
Vue.directive('scroll', {
  inserted: function (el, binding) {
    let func = function(evt) {
      if(binding.value(evt, el)) {
        window.removeEventListener('scroll', func)
      }
    };
    window.addEventListener('scroll', func)
  }
});

Vue.filter("truncate", function(text, length, clamp = "...") {
  let node = document.createElement("div");
  node.innerHTML = text;
  let content = node.textContent;
  return content.length > length ? content.slice(0, length) + clamp : content;
});

import axios from "axios";

// add axios to use later in components
Vue.prototype.$axios = axios;

// set header for ajax request check pass on backend
Vue.prototype.$axios.defaults.headers.common["IS_AJAX_REQUEST"] = "TRUE";

/**
 * Merge new properties to computed properties of a Vue instance
 *
 * @param propertiesToAdd {Array<{}>} object/objects to merge
 * @returns void
 */
const $composeComputed = function(...propertiesToAdd) {
  this.$set(
    this.$options,
    "computed",
    composeComputed(this.$options.computed, propertiesToAdd)
  );
};

// Add functionality to Vue
Vue.prototype.$composeComputed = $composeComputed;

let i18n = instanceI18n(locale);

// Import and add Vuetify to project, i18n as dependency
let vuetify = newVuetify(i18n);

// Перевод строк через _() вместо $t(), как на бэкэнде
Vue.mixin({
  methods: {
    _: function() {
      return this.$t.apply(this, arguments);
    },
    /** pluralization */
    _pl: function() {
      return this.$tc.apply(this, arguments);
    },
    getWindow() { return window; }
  },
});

// Vue.config.productionTip = false;

// init common data
let PHP_VIEW_VARS = {};
try {
  PHP_VIEW_VARS = { ...window.__HM.php_view_vars };
} catch (e) {
  console.log(`No data in vue_data :(`);
}

let PHP_CONFIG_COLORS = {};
try {
  PHP_CONFIG_COLORS = { ...window.__HM.php_config_colors };
} catch (e) {
  console.log(`No data in php_config_colors :(`);
}

// import all components
import components from "./components";

// import Vuex store
import store from "./store";

// Vue.config.errorHandler = function(err, vm, info) {
//   // handle error
//   // `info` is a Vue-specific error info, e.g. which lifecycle hook
//   // the error was found in. Only available in 2.2.0+
//   console.error(err);
//   console.info(`Vue Instance`, vm);
//   console.info(`Error happened at ${info}.`);
// };

// // Add Sentry for development purposes
// import * as Sentry from "@sentry/browser";

// if (process.env.NODE_ENV === "development") {
//   Sentry.init({
//     dsn: "https://e756cdc8ce9e475881397456d824dd91@sentry.io/1307986",
//     integrations: [new Sentry.Integrations.Vue({ Vue })]
//   });
// }

if (process.env.NODE_ENV === "development") {
  // performance tracing
  Vue.config.performance = true;
}

// Add logger functionality
import VueLogger from "vuejs-logger";
const isProduction = process.env.NODE_ENV === "production";

const options = {
  isEnabled: true,
  logLevel: isProduction ? "error" : "debug",
  stringifyArguments: false,
  showLogLevel: true,
  showMethodName: true,
  separator: "|",
  showConsoleColors: true
};

/**
 * Проброс палитры цветов и в store, чтобы подключенные компоненты
 * на любом уровне могли получить к ним доступ с помощью mixins: [VueMixinConfigColors],
 */
store.dispatch(GlobalActions.setColors, PHP_CONFIG_COLORS);
store.dispatch(GlobalActions.resetLoading);

window.__HM.vuexStore = store;

/**
 *  these statements will print nothing if the logLevel is set to 'fatal'. But they will compile just fine.
    this.$log.debug('test', 'bar')
    this.$log.info('test')
    this.$log.warn('test')
    this.$log.error('test', 'foo')
    this statement will print if the logLevel is set to 'fatal'
    this.$log.fatal('test', 'bar', 123)
 */
Vue.use(VueCompositionApi);
Vue.use(VueLogger, options);


import { mapState } from "vuex";

Vue.prototype.$isMobileApp = PHP_VIEW_VARS["isMobileApp"];

/** Vue.options.componentsFixed добавлено вызовом monkeyPatchVue() */
monkeyPatchVuetifyComponents(vueComponentsImported);

const app = new Vue({
  vuetify,
  store,
  el: "#hm-vue-app",
  // comments: true, // не работает
  components: { ...components },
  mixins: [VueMixinConfigColors],
  i18n,
  data() {
    return {
      appContentFullscreen: PHP_VIEW_VARS["appContentFullscreen"] || false,
      isDrawerShown: false,
      view: { ...PHP_VIEW_VARS },
      // стало configColors
      // colors: { ...PHP_CONFIG_COLORS },
      inDevelopment: process.env.NODE_ENV === "development",
      drawer: null,
      items: [
        { title: 'Home', icon: 'dashboard' },
        { title: 'About', icon: 'question_answer' }
      ],
      miniNavMenu: true,
      right: null,
      mainMenuDropdown: false,
      activeUser: false,
      testParent: false,
    };
  },
  watch: {
    /**
     * записываю в localstorage состояние меню, что бы запоминать его и после перезагрузки отрисовывать его состояние
     * @param data
     */
    miniNavMenu(data) {
      localStorage.setItem('miniNavMenu', data)
    },
    isDrawerShown(value) {
      if (!this.miniNavMenu && !value) {
        this.miniNavMenu = true;
        this.isDrawerShown = true;
      }
    },
  },
  /**
   * Предлагаю добавлять к именам свойств префикс appComputed,
   * чтобы можно было потом легче найти по коду /d9k
   */
  computed: {
    ...mapState({
      isContentBeingHovered: state => state.global.sidebarToHover
    }),

    appComputedNavMenuWidth() {
      return 300;
    },

    appComputedNavMenuMiniWidth() {
      return 80;
    },

    appComputedHmSidebarWidth() {
      return this.$vuetify.breakpoint.width <= 340 ? 260 : 326;
    },

    appComputedContentMargins() {
      let contentMargins = {
        right: 0,
        left: 0,
      };

      if (this.appComputedHmSidebarShareWidth) {
        contentMargins.right = this.appComputedHmSidebarWidth;
      }

      if (this.appComputedNavMenuShareWidth) {
        contentMargins.left = this.appComputedNavMenuWidth;
      } else {
        contentMargins.left = this.appComputedNavMenuMiniWidth;
      }

      return contentMargins;
    },

    appComputedContentMarginsWidth() {
      let m = this.appComputedContentMargins;
      return m.left + m.right;
    },

    /**
     * Свойство перерасчёта ширины и отступов footer'а при открывании sidebar'ов
     */
    appComputedFooterStyle() {
      let style = {};
      let footerMargins = this.appComputedContentMargins;
      style.marginLeft = addPx(footerMargins.left);
      style.marginRight = addPx(footerMargins.right);

      // style.width = `calc(100%-${widthDifference}px)`;

      return style;
    },

    appComputedNavMenuOpened() {
      return !this.miniNavMenu;
    },

    /**
     * Боковые панели делят ширину с контентом при открытии (иначе открываются поверх него)
     */
    appComputedShareWidthThreshold() {
      // > 1264px
      return this.$vuetify.breakpoint.width >= 1400;
    },

    appComputedNavMenuShareWidth() {
      return this.appComputedNavMenuOpened && this.appComputedShareWidthThreshold;
    },

    appComputedHmSidebarShareWidth() {
      return this.appComputedHmSidebarOpened && this.appComputedShareWidthThreshold;
    },

    appComputedFooterWidth() {
      let footerMargins = this.appComputedContentMargins;
      let footerWidth = this.$vuetify.breakpoint.width - footerMargins.left - footerMargins.right;
      return footerWidth;
    },

    appComputedFooterMediumWidth() {
      return this.appComputedFooterWidth < 1400;
    },

    appComputedFooterSmallWidth() {
      return this.$vuetify.breakpoint.smAndDown
    },

    appComputedHmSidebarOpened() {
      let sidebarGetters = this.$store.getters["sidebars/sidebarItems"];
      for(let i in sidebarGetters) {
        if(sidebarGetters[i].opened) {
          return true;
        }
      }
      return false;
    },

    appComputedAppCssClasses() {
      return {
        "app-content-fullscreen": this.appContentFullscreen,

        "breakpoint-sm-and-down": this.$vuetify.breakpoint.smAndDown,

        "hm-mobile-app": this.$isMobileApp,
        "hm-sidebar-opened": this.appComputedHmSidebarOpened,
        "hm-sidebar-share-width": this.appComputedHmSidebarShareWidth,

        "nav-menu-opened": this.appComputedNavMenuOpened,
        "nav-menu-share-width": this.appComputedNavMenuShareWidth,
      };
    },

    appComputedContentWrapperClasses() {
      return {
        'layout-content-full-width': this.view.layoutContentFullWidth,
      };
    },

    appComputedMaterialMinHeight() {
      return "400px";
    },

    appComputedMaterialMaxHeight() {
      return "calc(100vh - 130px)";
    },

  },
  created() {
    this.activeUser = this.$store.getters["user/GET_DATA_USER"] !== null ? true : false; // если пользователь есть, меню отрисовываем иначе не показываем
    try {
      if (process.env.NODE_ENV === "development") {
        setTimeout(() => {
          let ZFDebugger = document.getElementById("ZFDebug_debug");
          if (ZFDebugger) ZFDebugger.style.visibility = "hidden";
        });
      }
    } catch (err) {
      console.warn(err);
    };
    if(this.appComputedShareWidthThreshold) {
      //записываем при загрузке состояние меню
      this.miniNavMenu = localStorage.getItem('miniNavMenu') !== null ? JSON.parse(localStorage.getItem('miniNavMenu')) : true
    }
  },
  methods: {
    appMethodSort(arr, by) {
      // .sort меняет исходный массив. Чтобы не было бесконечного перезапуска рендеринга, копируем
      let arrCopy = JSON.parse(JSON.stringify(arr));

      let sorted = arrCopy.sort((a,b)=>{return a[by]>b[by]?1:(a[by]<b[by]?-1:0)});
      return sorted;
    },
    toggleDrawer() {
      this.miniNavMenu = !this.miniNavMenu;
      // this.isDrawerShown = !this.isDrawerShown;
    },
    toggleMenu() {
      this.isDrawerShown = !this.isDrawerShown;
    },
    handleScroll(evt, el) {
      if(window.scrollY + window.innerHeight >= document.body.clientHeight - 200 ) {
// el.style.position = `sticky`
      } else if(window.scrollY <= window.innerHeight) {
        // el.style.height = `absolute`
      }
    },
    appMethodHideCloseAndSidebar() {
      this.miniNavMenu = true;
      this.appContentFullscreen = false;
      this.$store.dispatch("sidebars/closeSidebar");
    },
  },
});

import events from "./utilities/Event";
import VueMixinConfigColors from "./utilities/mixins/VueMixinConfigColors";

// add modal to prototype
Vue.prototype.$confirmModal = ({
  text,
  title,
  confirmText,
  cancelText,
  persistent
}) => {
  app.$root.$emit(events.SHOW_MODAL_CONFIRM, {
    text,
    title,
    confirmText,
    cancelText,
    persistent
  });
  return new Promise((resolve, reject) => {
    app.$root.$on(events.CLOSE_MODAL_CONFIRM, () => reject());
    app.$root.$on(events.ACCEPT_MODAL_CONFIRM, () => resolve());
  });
};


