// see https://forum.vuejs.org/t/import-all-components-from-a-specific-folder/9980/5
// see https://vuejs.org/v2/guide/components-registration.html#Automatic-Global-Registration-of-Base-Components

const requireComponents = require.context("./", false, /icon[A-Z]\w+\.vue$/);

// export default components.keys().map(x => components(x))

let components = {};

requireComponents.keys().forEach(fileName => {
  const componentName =
    // Gets the file name regardless of folder depth
    fileName
      .split("/")
      .pop()
      .replace(/\.\w+$/, "");

  const componentConfig = requireComponents(fileName);

  components[componentName] = componentConfig.default || componentConfig;
});

export default components;
