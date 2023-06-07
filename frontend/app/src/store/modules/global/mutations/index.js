export default {
  DISABLE_MAIN_LAYOUT: state => {
    state.showMainLayout = false;
  },
  SIDEBAR_IS_HOVERING: (state, isHovering) => {
    state.sidebarToHover = isHovering;
  },
  SET_LOADING: (state, { componentName, isLoading }) => {
    if (isLoading) {
      state.loadingComponents.push(componentName);
    } else {
      // удаление элемента массива
      state.loadingComponents = state.loadingComponents.filter(
        item => item !== componentName
      );
    }
  },
  RESET_LOADING: (state) => {
    state.loadingComponents = [];
  },
  SET_COLORS: (state, colors) => {
    state.colors = colors;
  },
};
