export let vueComponentsImported: any = {};

export let vueComponentFnDefault: Function;
export let vueComponentFnModded: Function;

export default function monkeyPatchVue(Vue: any) {
  let prefix = 'patchVue: ';

  /** Vue.options.components doesn't always work, see https://forum.vuejs.org/t/list-registered-vue-components/7556/13 */

  if (vueComponentFnDefault) {
    console.error(prefix, 'already patched');
  }

  vueComponentFnDefault = Vue.component.bind(Vue);

  /** @see node_modules/vue/src/core/global-api/assets.js */
  vueComponentFnModded = function(id: string, component: any) {
    let result = vueComponentFnDefault(id, component);
    vueComponentsImported[id] = component;

    return result;
    // console.log('modded Vue.component():', id, component);
  };

  //@ts-ignore
  Vue.component = vueComponentFnModded;
}
