// see https://forum.vuejs.org/t/import-all-components-from-a-specific-folder/9980/5
// see https://vuejs.org/v2/guide/components-registration.html#Automatic-Global-Registration-of-Base-Components

import {capitalize} from "../strings";

const requireComponents = require.context("./", false, /icon[A-Z]\w+\.vue$/);

// export default components.keys().map(x => components(x))

let componentsName = [];

requireComponents.keys().forEach(fileName => {
  const componentName =
    // Gets the file name regardless of folder depth
    fileName.replace('./icon','').replace('.vue','')

  componentsName.push(componentName);
});

export { componentsName };
