import Vue from "vue";

export default {
  changeSidebarState(state, payload) {
    for (let item in state.items) {
      if (state.items.hasOwnProperty(item) && item !== payload.name) {
        Vue.set(state.items[item], "opened", false);
      }
    }
    setTimeout(() => {
      Vue.set(state.items[payload.name], "opened", payload.options.opened);
    });
  },
  registerSidebar(state, payload) {
    Vue.set(state.items, payload.name, payload.options);
  },
  closeSidebar(state) {
    for (let item in state.items) {
      if (state.items.hasOwnProperty(item)) {
        Vue.set(state.items[item], "opened", false);
      }
    }
  },
};
