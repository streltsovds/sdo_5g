const subjects = {
  show: false,

};

const getters = {};
const mutations = {
  SET_OPEN_CLOSE(state) {
    state.show = !state.show;
  },
};

const actions = {

};

export default {
  namespaced: true,
  state: subjects,
  getters,
  mutations,
  actions
};
