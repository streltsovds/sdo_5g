export default {
  configColor: (state) => (colorName) => {
    if (!state.colors) {
      return undefined;
    }

    return state.colors[colorName];
  },
  configColors: (state) => {
    return state.colors;
  },
  isLoading: (state) => {
    return state.loadingComponents.length > 0;
  },
  componentIsLoading: (state) => (moduleName) => {
    return state.loadingComponents.includes(moduleName);
  },
};
