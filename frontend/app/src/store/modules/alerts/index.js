let alerts = [];

const getters = {};
const mutations = {
  ADD_ALERT(state, data) {
    state.push({
      type: data.type,
      text: data.text
    });
  },
  REMOVE_ALERT(state, data) {
    let i = state.indexOf(data);
    if (i !== -1) {
      state.splice(i, 1);
    }
  }
};

const actions = {
  addAlert({ commit }, data) {
    commit("ADD_ALERT", data);
  },
  removeAlert({ commit }, data) {
    commit("REMOVE_ALERT", data);
  },
  addErrorAlert({ commit }, data) {
    commit("ADD_ALERT", {
      type: "error",
      text: data
    });
  },
  addSuccessAlert({ commit }, data) {
    commit("ADD_ALERT", {
      type: "success",
      text: data
    });
  },
  addInfoAlert({ commit }, data) {
    commit("ADD_ALERT", {
      type: "info",
      text: data
    });
  }
};

export default {
  namespaced: true,
  state: alerts,
  getters,
  mutations,
  actions
};
