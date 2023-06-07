import Vue from 'vue';
import MobileApp from './MobileApp.vue';
let locale = "ru";
// import Vuetify from 'vuetify';

import newVuetify from "../src/vuetify";
import instanceI18n from "../src/i18n/instance";

import '@ionic/core/css/core.css';
import '@ionic/core/css/ionic.bundle.css';
import "./assets/fonts/materialicons/material-icons.css";

import "../src/scss/app.scss";
import "./scss/mobileApp.scss";

import IonicVue from '@ionic/vue';
import router from "./router";

let i18n = instanceI18n(locale);
let vuetify = newVuetify(i18n);

Vue.use(IonicVue);

Vue.mixin({
    methods: {
        _:function() {
            return this.$t.apply(this, arguments);
        }
    }
});


Vue.config.productionTip = false;
Vue.prototype.$isMobileApp = true,

new Vue({
  vuetify,
  i18n,
  render: (h) => h(MobileApp),
  router
}).$mount('#mobileApp');

/*
///////////////////////////////////////////////////////////
// Storage
let _storage = {};
let storage = new Proxy(_storage, {
  get(target, prop) {    return localStorage[prop] ? JSON.parse(localStorage[prop]) : {};  },
  set(target, prop, value) { localStorage[prop] = JSON.stringify(value); return true;  }
});
window.storage = storage;
////////////////////////////////////////////////////////////
*/

