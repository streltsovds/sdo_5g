const ActionsFactory = (action_name, module_prefix) => {
  let ret = {};
  ret[action_name] = (context, payload) => {
    const actionName = "register";
    context.commit(module_prefix + actionName, payload);
  };
  return ret;
};

/**
 * Получить название пространства имён текущего модуля vuex
 * @see https://github.com/vuejs/vuex/issues/1244#issuecomment-456221734
 *
 * Note: На данный момент не используется, т. к. не заработало в getter-ах,
 * см. SET_NAMESPACE вручную при создании компонента
 **/
function getModuleNamespace(store, state) {
  const moduleNamespace = Object.keys(store._modulesNamespaceMap)
    .find(path => store._modulesNamespaceMap[path].context.state === state);
  if (typeof mouleNamespace === 'string') {
    return  moduleNamespace.slice(0, -1).split('/');
  }
}

export {
  ActionsFactory,
  getModuleNamespace
}
