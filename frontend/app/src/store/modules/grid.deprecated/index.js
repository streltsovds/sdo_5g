let grid = {
  items: {}
};

const getters = {};
const mutations = {
  SET_GRID_DATA(state, data) {
    if (!state.items.hasOwnProperty(data.gridId)) state.items[data.gridId] = {};
    state.items[data.gridId] = { ...state.items[data.gridId], ...data };
  }
};

const actions = {
  setGridDataByGridId({ commit }, data) {
    commit("SET_GRID_DATA", data);
  }
};

export default {
  namespaced: true,
  state: grid,
  getters,
  mutations,
  actions
};
