import Vue from "vue";
import Vuex from "vuex";
import createPersistedState from "vuex-persistedstate";

import modules from "./modules";

// Разрешить отладку в Vue Dev Tools для production
// (дублирование из app.js, потому что, похоже, import происходит раньше других действий)
Vue.config.devtools = true;

// лист модулей которые не надо кэшировать
const notIncludeList = ["grid", "kbase", "subject", "sidebars"];

Vue.use(Vuex);

const persistedstateOptions = {
  key:
    (window.__HM && window.__HM.vuexCacheKey) ||
    "dataUser",
  paths: Object.keys(modules).filter(x => !notIncludeList.includes(x))
};

export default new Vuex.Store({
  //strict: process.env.NODE_ENV === "development",
  modules,
  plugins: [createPersistedState(persistedstateOptions)]
});
