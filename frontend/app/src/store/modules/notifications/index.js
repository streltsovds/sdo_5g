// import uniqueid from "lodash.uniqueid";
import lodash from "lodash";
let uniqueId = lodash.uniqueId

import STATUS from "../../../components/els/hm-notifications/partials/status";

let notifications = {
  items: []
};

const getters = {};
const mutations = {
  ADD_NOTIFICATIONS(state, data) {
    let item = {
      id: uniqueId(),
      type: data.type,
      text: data.text,
    };
    state.items.push(item);
  },
  REMOVE_NOTIFICATIONS(state, data) {
    let i = state.items.findIndex(item => {
      return item.id === data;
    });
    if (i !== -1) {
      state.items.splice(i, 1);
    }
  },
  RESET_NOTIFICATIONS(state) {
    state.items = [];
  },
};

const actions = {
  addNotification({ commit }, data) {
    commit("ADD_NOTIFICATIONS", data);
  },
  removeNotification({ commit }, data) {
    commit("REMOVE_NOTIFICATIONS", data);
  },
  resetNotifications({ commit }) {
    commit("RESET_NOTIFICATIONS");
  },
  addWarningNotification({ commit }, data) {
    commit("ADD_WARNING", {
      type: STATUS.WARNING,
      text: data
    });
  },
  addErrorNotification({ commit }, data) {
    commit("ADD_NOTIFICATIONS", {
      type: STATUS.ERROR,
      text: data
    });
  },
  addSuccessNotification({ commit }, data) {
    commit("ADD_NOTIFICATIONS", {
      type: STATUS.SUCCESS,
      text: data
    });
  },
  addInfoNotification({ commit }, data) {
    commit("ADD_NOTIFICATIONS", {
      type: STATUS.INFO,
      text: data
    });
  }
};

export default {
  namespaced: true,
  state: notifications,
  getters,
  mutations,
  actions
};
