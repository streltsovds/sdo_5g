const proctoring = {
  url: '',
  name: '',
  typeMaterial: ""
};

const getters = {
  GET_URL(state) {
    return state.url
  },
  GET_NAME(state) {
    return state.name
  },
  GET_TYPE_MATERIAL(state) {
    return state.typeMaterial
  }
};
const mutations = {
  SET_URL(state, data) {
    state.url = data;
  },
  SET_NAME(state, data) {
    state.name = data;
  },
  SET_TYPE_MATERIAL(state, data) {
    state.typeMaterial = data;
  }
};

const actions = {

};

export default {
  namespaced: true,
  state: proctoring,
  getters,
  mutations,
  actions
};
