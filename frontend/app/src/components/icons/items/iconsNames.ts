const requireComponents = require.context("./", false, /icon[A-Z]\w+\.vue$/);

let componentsName: string[] = [];

requireComponents.keys().forEach(fileName => {
  const componentName =
    // Gets the file name regardless of folder depth
    fileName.replace('./icon','').replace('.vue','');

  componentsName.push(componentName);
});

export { componentsName };
