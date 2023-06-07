let kbase = {
  showItems: false,
  searchFilters: {
    search_query: null,
    classifiers: []
  }
};

const getters = {};
const mutations = {
  RESET_SEARCH(state) {
    state.showItems = false;
    state.searchFilters.search_query = "";
    state.searchFilters.classifiers = [];
  },
  SET_SEARCH_FILTERS(state, data) {
    state.showItems = true;
    state.searchFilters = {
      search_query: data.search_query,
      classifiers: data.classifiers
    };
  }
};

const actions = {
  setSearchFilters({ commit }, data) {
    commit("SET_SEARCH_FILTERS", data);
  },
  hideItems({ commit }) {
    commit("HIDE_ITEMS");
  },
  resetSearch({ commit }) {
    commit("RESET_SEARCH");
  }
};

export default {
  namespaced: true,
  state: kbase,
  getters,
  mutations,
  actions
};
