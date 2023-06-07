let subject = {
  showItems: false,
  searchFilters: {
    search_query: null,
    classifiers: []
  }
};

const getters = {
  GET_CLASSIFIERS( state ) {
    return state.searchFilters.classifiers
  },
  GET_SEARCH_QUERY( state ) {
    return state.searchFilters.search_query
  }
};
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
  },
  ADD_CLASSIFIERS(state, data) {
    state.searchFilters.classifiers = data
  },
  ADD_SEARCH_QUERY ( state , data ) {
    state.searchFilters.search_query = data
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
  },
  addClassifiers({ commit }, data) {
    commit("ADD_CLASSIFIERS", data)
  },
  addSearchQuery ({ commit }, data) {
    commit("ADD_SEARCH_QUERY", data)
  },
};

export default {
  namespaced: true,
  state: subject,
  getters,
  mutations,
  actions
};
