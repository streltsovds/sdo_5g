import actions from "./../const/actions";

export default {
  [actions.disableMainLayout]: ({ commit }) => {
    commit("DISABLE_MAIN_LAYOUT");
  },
  [actions.sidebarIsHovering]: ({ commit }, isHovering) => {
    commit("SIDEBAR_IS_HOVERING", isHovering);
  },
  [actions.setLoadingOn]: ({ commit }, componentName = "default") => {
    commit("SET_LOADING", { componentName, isLoading: true });
  },
  [actions.setLoadingOff]: ({ commit }, componentName = "default") => {
    commit("SET_LOADING", { componentName, isLoading: false });
  },
  [actions.setColors]: ({ commit }, colors) => {
    commit("SET_COLORS", colors);
  },
  [actions.resetLoading]: ({commit}) => {
    commit("RESET_LOADING");
  },
};
