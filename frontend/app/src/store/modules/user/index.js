import axios from "axios";

let user = {
  id: null
};

const getters = {
  GET_DATA_USER: state => state.id
};
const mutations = {
  RESET_DATA_USER(state) {
    state.id = null;
  },
  SET_DATA_USER(state, data) {
    state.id = data.MID;
  }
};

const actions = {
  initUser({ commit }) {
    return axios
      .get("/user/ajax/current-user-data")
      .then(response => {
        if (response.status === 200 && response.data)
          commit("SET_DATA_USER", response.data);
      })
      .catch(error => {
        console.error(error);
      });
  },
  logoutUser({ commit }) {
    axios.get("/logout").then(() => {
      commit("RESET_DATA_USER");
      window.location.href = "/";
    });
  }
};

export default {
  namespaced: true,
  state: user,
  getters,
  mutations,
  actions
};
