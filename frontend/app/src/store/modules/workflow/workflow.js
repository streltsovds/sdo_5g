const getters = {};
const mutations = {
  RESET_IS_SUBMIT(state) {
    state.isSubmit = false;
  },
  SET_IS_SUBMIT(state) {
    state.isSubmit = true;
  }
};

const actions = {
  setIsSubmit({ commit }) {
    commit("SET_IS_SUBMIT");
  },
  resetIsSubmit({ commit }) {
    commit("RESET_IS_SUBMIT");
  }
};

export default {
  namespaced: true,
  getters,
  mutations,
  actions
};
