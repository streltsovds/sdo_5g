export const V_TOOLTIP_OPEN_DELAY = 350;

export default function monkeyPatchVuetifyComponents(components: any) {
  components = components || {};

  let prefix = 'patchVuetifyComponents: ';

  const patchComponentByName = (componentName: string, callback: Function) => {
    let component = components[componentName];

    if (component) {
      callback(component);
    } else {
      console.error(prefix, `Can't patch ${componentName}: not found`);
    }
  };

  const patchComponentsByName = (componentNameTocallback: Object) => {
    for (let [componentName, callback] of Object.entries(componentNameTocallback)) {
      patchComponentByName(componentName, callback);
    }
  };

  patchComponentsByName({
    VTooltip: (VTooltip: any) => {
      VTooltip.options.props.openDelay.default = V_TOOLTIP_OPEN_DELAY;
    },
  });
}
